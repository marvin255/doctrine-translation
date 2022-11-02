<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\Locale;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Marvin255\DoctrineTranslationBundle\Locale\Locale;
use Marvin255\DoctrineTranslationBundle\Locale\LocaleType;
use Marvin255\DoctrineTranslationBundle\Tests\BaseCase;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

/**
 * @internal
 */
class LocaleTypeTest extends BaseCase
{
    /**
     * @dataProvider provideConvertToPHPValue
     */
    public function testConvertToPHPValue(mixed $value, mixed $reference): void
    {
        /** @var AbstractPlatform&MockObject */
        $platformMock = $this->getMockBuilder(AbstractPlatform::class)->getMock();

        $type = $this->createLocaleType();

        if ($reference instanceof Throwable) {
            $this->expectException(\get_class($reference));
            $type->convertToPHPValue($value, $platformMock);
        } elseif ($reference === null) {
            $phpValue = $type->convertToPHPValue($value, $platformMock);
            $this->assertNull($phpValue);
        } else {
            /** @var Locale */
            $phpValue = $type->convertToPHPValue($value, $platformMock);
            $this->assertSame($reference, $phpValue->getFull());
        }
    }

    public function provideConvertToPHPValue(): array
    {
        return [
            'locale' => [
                'en-US',
                'en-US',
            ],
            'null locale' => [
                null,
                null,
            ],
            'not a string' => [
                123456,
                new ConversionException('123456'),
            ],
            'empty string' => [
                '',
                new ConversionException('Expected format'),
            ],
        ];
    }

    /**
     * @dataProvider provideConvertToDatabaseValue
     */
    public function testConvertToDatabaseValue(mixed $value, mixed $reference): void
    {
        /** @var AbstractPlatform&MockObject */
        $platformMock = $this->getMockBuilder(AbstractPlatform::class)->getMock();

        $type = $this->createLocaleType();

        if ($reference instanceof Throwable) {
            $this->expectException(\get_class($reference));
            $type->convertToDatabaseValue($value, $platformMock);
        } else {
            $phpValue = $type->convertToDatabaseValue($value, $platformMock);
            $this->assertSame($reference, $phpValue);
        }
    }

    public function provideConvertToDatabaseValue(): array
    {
        return [
            'locale object' => [
                new Locale('en-US'),
                'en-US',
            ],
            'null locale' => [
                null,
                null,
            ],
            'not a locale object' => [
                '',
                new ConversionException(),
            ],
        ];
    }

    public function testGetName(): void
    {
        $type = $this->createLocaleType();

        $this->assertSame(LocaleType::LOCALE_TYPE, $type->getName());
    }

    /**
     * @psalm-suppress InternalMethod
     */
    protected function createLocaleType(): LocaleType
    {
        return new LocaleType();
    }
}
