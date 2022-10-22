<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\Locale;

use InvalidArgumentException;
use Marvin255\DoctrineTranslationBundle\Locale\LocaleValue;
use Marvin255\DoctrineTranslationBundle\Tests\BaseCase;

/**
 * @internal
 */
class LocaleValueTest extends BaseCase
{
    public function testEmptyConstructorValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new LocaleValue('');
    }

    public function testSpacesOnlyConstructorValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new LocaleValue('       ');
    }

    /**
     * @dataProvider provideGetLanguage
     */
    public function testGetLanguage(string $locale, string $reference): void
    {
        $localeValue = new LocaleValue($locale);
        $language = $localeValue->getLanguage();

        $this->assertSame($reference, $language);
    }

    public function provideGetLanguage(): array
    {
        return [
            'only language' => [
                'en',
                'en',
            ],
            'with region' => [
                'en-US',
                'en',
            ],
            'wrong case' => [
                'eN-US',
                'en',
            ],
            'wrong space in the middle' => [
                'en -US',
                'en',
            ],
            'wrong space in the beginning' => [
                ' en-US',
                'en',
            ],
        ];
    }

    /**
     * @dataProvider provideGetRegion
     */
    public function testGetRegion(string $locale, string $reference): void
    {
        $localeValue = new LocaleValue($locale);
        $region = $localeValue->getRegion();

        $this->assertSame($reference, $region);
    }

    public function provideGetRegion(): array
    {
        return [
            'only language' => [
                'en',
                '',
            ],
            'with region' => [
                'en-US',
                'US',
            ],
            'wrong case' => [
                'en-Us',
                'US',
            ],
            'wrong space in the middle' => [
                'en- US',
                'US',
            ],
            'wrong space in the end' => [
                'en-US ',
                'US',
            ],
        ];
    }

    /**
     * @dataProvider provideGetFull
     */
    public function testGetFull(string $locale, string $reference): void
    {
        $localeValue = new LocaleValue($locale);
        $full = $localeValue->getFull();

        $this->assertSame($reference, $full);
    }

    public function provideGetFull(): array
    {
        return [
            'only language' => [
                'en',
                'en',
            ],
            'with region' => [
                'en-US',
                'en-US',
            ],
            'wrong case' => [
                'eN-Us',
                'en-US',
            ],
            'wrong space in the middle' => [
                'en - US',
                'en-US',
            ],
            'wrong space in the end' => [
                ' en-US ',
                'en-US',
            ],
        ];
    }
}
