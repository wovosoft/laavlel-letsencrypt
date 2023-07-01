<?php

namespace Wovosoft\TypescriptTransformer;

use Illuminate\Support\Collection;

class TypescriptType
{
    public function __construct(
        public string      $namespace,
        public string      $model,
        public string      $shortName,
        public ?Collection $types = null
    )
    {
    }

    public function generate(): string
    {
        $contents = "\texport interface $this->shortName {\n";
        $this->types?->each(function (string $value, string $key) use (&$contents) {
            $contents .= "\t\t$key: $value;\n";
        });
        $contents .= "\t}\n\n";
        return $contents;
    }
}
