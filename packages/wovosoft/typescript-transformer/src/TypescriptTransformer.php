<?php

namespace Wovosoft\TypescriptTransformer;


use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Column;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use ReflectionMethod;

class TypescriptTransformer
{
    /**
     * @param array<class-string<Model>> $modelClasses
     */
    public function __construct(public array $modelClasses = [])
    {

    }

    private function getType(Model $model, Column $column): string
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
     * @throws Exception
     */
    public function transform(string $modelClass): Collection
    {
        /** @var Model $model */
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
     * @throws Exception
     */
    public function run(): void
    {
        \File::ensureDirectoryExists(resource_path("js/types"));
        $filePath = resource_path("js/types/models.d.ts");
        if (\File::exists($filePath)) {
            \File::put($filePath, "");
        }

        foreach ($this->modelClasses as $modelClass) {
            $slices = str($modelClass)->replace("\\", ".")->explode(".");
            $typeName = $slices->last();
            $contents = "interface $typeName{\n";

            $fields = $this->transform($modelClass);
            $fields->each(function ($value, $key) use (&$contents) {
                $contents .= "\t$key: $value\n";
            });
            $contents .= "}\n\n";


            \File::append($filePath, $contents);
        }
    }


    /**
     * @throws \ReflectionException
     */
    public function getModelRelations(string|Model $model): Collection
    {
        $reflection = new \ReflectionClass($model);
        $relationMethods = collect($reflection->getMethods())->filter(function (ReflectionMethod $method) {
            $returnType = $method->getReturnType();
            return $returnType !== null && is_subclass_of($returnType->getName(), Relation::class);
        });

        $relationMethods->each(function (ReflectionMethod $reflectionMethod) use ($model) {
            $relation = $model->{$reflectionMethod->getName()}();
            dump(get_class($relation->getRelated()));
        });

        return $relationMethods;
    }
}
