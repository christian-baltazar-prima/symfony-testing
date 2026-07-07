<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Service\FooService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FooServiceTest extends TestCase
{

    #[Test]
    public function foo_get_data(): void
    {
        $test = new FooService(['foo' => 'bar']);
        $this->assertEquals(['foo' => 'bar'], $test->getData());
        $this->assertEmpty($test->getData('nonexistent'));
    }

}