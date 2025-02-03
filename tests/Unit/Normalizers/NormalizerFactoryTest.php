<?php

namespace Kudashevs\RakePhp\Tests\Unit\Normalizers;

use Kudashevs\RakePhp\Exceptions\InvalidNormalizerCase;
use Kudashevs\RakePhp\Normalizers\Normalizer;
use Kudashevs\RakePhp\Normalizers\NormalizerFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class NormalizerFactoryTest extends TestCase
{
    private NormalizerFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new NormalizerFactory();
    }

    #[Test]
    public function it_throws_an_exception_when_a_unknown_case(): void
    {
        $this->expectException(InvalidNormalizerCase::class);
        $this->expectExceptionMessage('unknown');

        (new NormalizerFactory())->for('wrong');
    }

    #[Test]
    #[DataProvider('provideDifferentCases')]
    public function it_can_create_for_different_cases(string $case): void
    {
        $preparator = $this->factory->for($case);

        $this->assertInstanceOf(Normalizer::class, $preparator);
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
