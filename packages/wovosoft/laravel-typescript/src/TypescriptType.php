<?php

namespace Wovosoft\LaravelTypescript;

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
        return "\texport interface $this->shortName {\n"
            . $this->types?->implode(fn(string $value, string $key) => "\t\t$key: $value;", "\n")
            . "\n\t}\n\n";
    }
}
