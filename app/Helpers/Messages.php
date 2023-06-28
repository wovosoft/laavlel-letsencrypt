<?php

namespace App\Helpers;

class Messages
{
    public static function withData(mixed $data = []): array
    {
        return [
            "key" => "data",
            "value" => $data
        ];
    }

    public static function withSuccess(?array $message = []): array
    {
        return [
            "key" => "notification",
            "value" => static::success($message)
        ];
    }

    public static function withFailed(\Throwable $throwable, ?array $message = []): array
    {
        return [
            "key" => "notification",
            "value" => static::failed($throwable, $message)
        ];
    }

    public static function success(?array $message = []): array
    {
        return array_merge([
            "title" => "Success",
            "message" => "Successfully Done",
            "variant" => "primary"
        ], $message);
    }

    public static function failed(\Throwable $throwable, ?array $message = []): array
    {
        return array_merge([
            "title" => "Failed",
            "message" => $throwable->getMessage(),
            "variant" => "danger"
        ], $message);
    }
}
