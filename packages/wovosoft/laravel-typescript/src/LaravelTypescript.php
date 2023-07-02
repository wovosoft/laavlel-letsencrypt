<?php

namespace Wovosoft\LaravelTypescript;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Column;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use ReflectionMethod;

class LaravelTypescript
{
    /**
     * @var Collection<int,class-string<Model>>
     */
    private Collection $modelClasses;

    public function __construct(
        public ?string $outputPath = null,
        public ?string $sourceDir = null,
    )
    {
        if (!$this->outputPath) {
            $this->outputPath = resource_path("js/types/models.d.ts");
        }
        if (!$this->sourceDir) {
            $this->sourceDir = app_path("Models");
        }
        $this->modelClasses = (new ModelFinder)->getModelsIn($this->sourceDir);
    }

    public function run(): void
    {
        File::ensureDirectoryExists(dirname($this->outputPath));
        File::put($this->outputPath, "");

        $this->getTypes()
            ->groupBy('namespace')
            ->each(function (Collection $types, string $namespace) {
                File::append($this->outputPath, "export namespace " . str($namespace)->replace("\\", ".")->value() . "{\n");
                $types->each(function (TypescriptType $typescriptType) {
                    File::append($this->outputPath, $typescriptType->generate());
                });
                File::append($this->outputPath, "}\n");
            });
    }

    /**
     * @return Collection<int,TypescriptType>
     */
    public function getTypes(): Collection
    {
        return $this->modelClasses->map(function (string $modelClass) {
            $reflection = (new \ReflectionClass($modelClass));

            $contents = $this->getModelFields($modelClass)
                ->merge($this->getModelRelations($modelClass))
                ->merge($this->getCustomAttributeTypes($modelClass))
                ->mapWithKeys(fn($value, $key) => [$key => $value]);

            return new TypescriptType(
                namespace: $reflection->getNamespaceName(),
                model: $modelClass,
                shortName: $reflection->getShortName(),
                types: $contents
            );
        });
    }


    /**
     * @param class-string<Model> $modelClass
     * @return Collection<string,array>
     * @throws Exception
     */
    public function getModelFields(string $modelClass): Collection
    {
        $model = new $modelClass();
        $columns = $model->getConnection()
            ->getDoctrineConnection()
            ->createSchemaManager()
            ->listTableColumns($model->getTable());

        /**
         * Model fields name should be exact like column name
         */
        return collect($columns)->mapWithKeys(fn(Column $column) => [
            $column->getName() => $this->toTypescript(item: $model, column: $column)
        ]);
    }

    private function isRelation(ReflectionMethod $method): bool
    {
        return $method->hasReturnType()
            && $method->getReturnType() instanceof \ReflectionNamedType
            && is_subclass_of($method->getReturnType()->getName(), Relation::class);
    }

    /**
     * @param class-string<Model>|Model $model
     * @throws \ReflectionException
     */
    public function getModelRelations(string|Model $model): Collection
    {
        if (is_string($model)) {
            $model = new $model();
        }

        return collect((new \ReflectionClass($model))->getMethods())
            ->filter(fn(ReflectionMethod $method) => $this->isRelation($method))
            ->mapWithKeys(fn(ReflectionMethod $reflectionMethod) => [
                str($reflectionMethod->getName())->snake()->value() => $this->getRelatedModelsType($model->{$reflectionMethod->getName()}())
            ]);
    }

    private function getRelatedModelsType(Relation $relation): string
    {
        $shorName = (new \ReflectionClass($relation->getRelated()))->getShortName();

        return match (get_class($relation)) {
            HasOne::class, HasOneThrough::class, BelongsTo::class, MorphOne::class => "{$shorName} | null",
            HasMany::class, HasManyThrough::class,
            BelongsToMany::class, MorphMany::class, MorphToMany::class => "{$shorName}[] | null",
            MorphOneOrMany::class => "{$shorName} | {$shorName}[] | null",
            default => "any"
        };

        //MorphPivot::class=>,
    }

    private function isMethodIsModelAttribute(ReflectionMethod $reflectionMethod): bool
    {
        $methodName = str($reflectionMethod->getName());
        return (
                $methodName->startsWith("get")
                && $methodName->endsWith("Attribute")
                && $methodName->value() !== "getAttribute"
            ) || ($reflectionMethod->getReturnType()?->getName() === Attribute::class);
    }

    /**
     * @description Attributes which returns Illuminate\Database\Eloquent\Casts\Attribute, that means new attribute format,
     * the callback of get should be explicitly defined. Otherwise, type will be unknown
     * @throws \ReflectionException
     */
    public function getCustomAttributeTypes(string|Model $model): Collection
    {
        if (is_string($model)) {
            $model = new $model();
        }

        return collect((new \ReflectionClass($model))->getMethods())
            ->filter(fn(ReflectionMethod $reflectionMethod) => $this->isMethodIsModelAttribute($reflectionMethod))
            ->mapWithKeys(function (ReflectionMethod $reflectionMethod) use ($model) {
                $methodName = str($reflectionMethod->getName());
                if ($methodName->startsWith("get") && $methodName->endsWith("Attribute")) {
                    return [
                        $methodName
                            ->after('get')
                            ->beforeLast('Attribute')
                            ->snake()
                            ->value() => $this->toTypescript($reflectionMethod->getReturnType())
                    ];
                }

                return [
                    str($reflectionMethod->getName())->snake()->value() => $this->toTypescript(
                        (new \ReflectionFunction($model->{$reflectionMethod->getName()}()->get))->getReturnType()
                    )
                ];
            });
    }

    private function toTypescript(\ReflectionUnionType|\ReflectionNamedType|Model|null $item = null, Column|null $column = null): string
    {
        if ($item instanceof Model) {
            $casts = $item->getCasts();

            if (in_array($column->getName(), array_keys($casts))) {
                /** @var class-string<\BackedEnum>|class-string<\UnitEnum> $castType */
                $castType = $casts[$column->getName()];

                if (enum_exists($castType)) {
                    return EnumType::toTypescript($castType);
                }

                return DatabaseType::toTypescript(Casts::type($castType));
            }

            return DatabaseType::toTypescript($column->getType());
        }


        if ($item instanceof \ReflectionUnionType) {
            return collect($item->getTypes())
                ->map(fn(\ReflectionNamedType $namedType) => PhpType::toTypescript($namedType->getName()))
                ->implode(" | ");
        }
        return PhpType::toTypescript($item?->getName());
    }
}
