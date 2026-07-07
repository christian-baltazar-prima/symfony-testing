<?php

declare(strict_types=1);

namespace App\Service;

class FooService
{
    public function __construct(private array $data) {}

    public function getData(?string $key = null): mixed
    {
        if ($key === null) {
            return $this->data;
        }

        return $this->data[$key] ?? null;
    }

    public function setData(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }
}