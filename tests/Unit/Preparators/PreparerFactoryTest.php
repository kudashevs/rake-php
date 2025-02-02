<?php

namespace Kudashevs\RakePhp\Tests\Unit\Preparators;

use Kudashevs\RakePhp\Exceptions\InvalidPreparerCase;
use Kudashevs\RakePhp\Preparers\Preparer;
use Kudashevs\RakePhp\Preparers\PreparerFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PreparerFactoryTest extends TestCase
{
    private PreparerFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new PreparerFactory();
    }

    #[Test]
    public function it_throws_an_exception_when_a_unknown_case(): void
    {
        $this->expectException(InvalidPreparerCase::class);
        $this->expectExceptionMessage('unknown');

        (new PreparerFactory())->for('wrong');
    }

    #[Test]
    #[DataProvider('provideDifferentCases')]
    public function it_can_create_for_different_cases(string $case): void
    {
        $preparator = $this->factory->for($case);

        $this->assertInstanceOf(Preparer::class, $preparator);
    }

    public static function provideDifferentCases(): array
    {
        return [
            'words case' => ['words'],
            'exclude case' => ['exclusions'],
            'include case' => ['inclusions'],
        ];
    }
}
