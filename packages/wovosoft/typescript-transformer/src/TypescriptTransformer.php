<?php

namespace Wovosoft\TypescriptTransformer;


use App\Models\Account;
use App\Models\User;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Column;
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

class TypescriptTransformer
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
        $this->modelClasses = (new ModelFinder())->getModelsIn(app_path("Models"));
    }

    public function run(): void
    {
        dump($this->getCustomAttributeTypes(new Account()));
//        \File::ensureDirectoryExists(dirname($this->outputPath));
//        \File::put($this->outputPath, "");
//
//        $this->getTypes()
//            ->groupBy('namespace')
//            ->each(function (Collection $types, string $namespace) {
//                \File::append($this->outputPath, "export namespace " . str($namespace)->replace("\\", ".")->value() . "{\n");
//                $types->each(function (TypescriptType $typescriptType) {
//                    \File::append($this->outputPath, $typescriptType->generate());
//                });
//                \File::append($this->outputPath, "}\n");
//            });
    }


    /**
     * @return Collection<int,TypescriptType>
     */
    public function getTypes(): Collection
    {
        return $this->modelClasses->map(function (string $modelClass) {
            $reflection = (new \ReflectionClass($modelClass));
            $namespace = $reflection->getNamespaceName();
            $contents = collect([]);

            $fields = $this->transform($modelClass);
            $fields->each(function ($value, $key) use (&$contents) {
                $contents->put($key, $value);
            });

            $this->getModelRelations($modelClass)->each(function (string $value, string $key) use (&$contents) {
                $contents->put($key, $value);
            });

            return new TypescriptType(
                namespace: $namespace,
                model: $modelClass,
                shortName: $reflection->getShortName(),
                types: $contents
            );
        });
    }

    public function getType(Model $model, Column $column): string
    {
        $casts = $model->getCasts();

        if (in_array($column->getName(), array_keys($casts))) {
            /** @var class-string<\BackedEnum>|class-string<\UnitEnum> $type */
            $type = $casts[$column->getName()];

            if (enum_exists($type)) {
                return collect($type::cases())->map(fn($option) => "\"$option->value\"")->implode(' | ');
            }

            return Type::toTypescript(Casts::type($type));
        }

        return Type::toTypescript($column->getType());
    }

    /**
     * @param class-string<Model> $modelClass
     * @return Collection<string,array>
     * @throws Exception
     */
    public function transform(string $modelClass): Collection
    {
        $model = new $modelClass();

        return collect(
            $model->getConnection()
                ->getDoctrineConnection()
                ->createSchemaManager()
                ->listTableColumns($model->getTable())
        )->mapWithKeys(function (Column $column) use ($model) {
            return [
                $column->getName() => $this->getType(
                    model: $model,
                    column: $column
                )
            ];
        });
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

        $reflection = new \ReflectionClass($model);
        return collect($reflection->getMethods())->filter(function (ReflectionMethod $method) {
            $returnType = $method->getReturnType();
            return $returnType !== null && is_subclass_of($returnType->getName(), Relation::class);
        })->mapWithKeys(function (ReflectionMethod $reflectionMethod) use ($model) {
            return [
                $reflectionMethod->getName() => $this->getRelatedClassType($model->{$reflectionMethod->getName()}())
            ];
        });
    }

    public function getRelatedClassType(Relation $relation): string
    {
        $shorName = (new \ReflectionClass($relation->getRelated()))->getShortName();

        return match (get_class($relation)) {
            HasOne::class, HasOneThrough::class, BelongsTo::class, MorphOne::class => $shorName,
            HasMany::class, HasManyThrough::class,
            BelongsToMany::class, MorphMany::class, MorphToMany::class => "{$shorName}[]",
            MorphOneOrMany::class => "{$shorName}|{$shorName}[]",
            default => "string"
        };

        //MorphPivot::class=>,
    }

    /**
     * @throws \ReflectionException
     */
    public function getCustomAttributeTypes(string|Model $model): Collection
    {
        if (is_string($model)) {
            $model = new $model();
        }

        $reflection = new \ReflectionClass($model);

        return collect($reflection->getMethods())->filter(function (ReflectionMethod $reflectionMethod) {
            $methodName = str($reflectionMethod->getName());
            return (
                    $methodName->startsWith("get")
                    && $methodName->endsWith("Attribute")
                    && $reflectionMethod->getName() !== "getAttribute"
                ) || ($reflectionMethod->getReturnType()?->getName() === Attribute::class);
        })->mapWithKeys(function (ReflectionMethod $reflectionMethod) {
            $methodName = str($reflectionMethod->getName());
            if ($methodName->startsWith("get") && $methodName->endsWith("Attribute")) {
                return [
                    $methodName
                        ->after('get')
                        ->beforeLast('Attribute')
                        ->snake()
                        ->value() => $reflectionMethod
                ];
            }
            return [
                $reflectionMethod->getName() => $reflectionMethod
            ];
        });
    }
}
