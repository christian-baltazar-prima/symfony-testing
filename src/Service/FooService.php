<?php

declare(strict_types=1);

namespace App\Service;

readonly class FooService
{
    public function __construct(private array $data) {}

    public function getData(?string $key = null): array
    {
        if ($key === null) {
            return $this->data;
        }

        return $this->data[$key] ?? [];
    }
}