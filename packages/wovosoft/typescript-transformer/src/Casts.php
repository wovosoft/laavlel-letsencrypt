<?php

namespace Wovosoft\TypescriptTransformer;

use Illuminate\Database\Eloquent\Casts\AsStringable;

class Casts
{
    public static array $options = [
        "int"                 => Type::INTEGER,
        "integer"             => Type::INTEGER,
        "array"               => Type::ARRAY,
        AsStringable::class   => Type::STRING,
        "boolean"             => Type::BOOLEAN,
        "collection"          => Type::ARRAY,
        "date"                => Type::DATE_MUTABLE,
        "datetime"            => Type::DATETIME_MUTABLE,
        "immutable_date"      => Type::DATE_IMMUTABLE,
        "immutable_datetime"  => Type::DATETIME_IMMUTABLE,
        "decimal:<precision>" => Type::FLOAT,
        "double"              => Type::FLOAT,
        "float"               => Type::FLOAT,
        "hashed"              => Type::STRING,
        "object"              => Type::OBJECT,
        "real"                => Type::FLOAT,
        "string"              => Type::STRING,
        "timestamp;"          => Type::STRING,
    ];

    public static function type(string $type): Type|string
    {
        return self::$options[$type] ?? $type;
    }
}
