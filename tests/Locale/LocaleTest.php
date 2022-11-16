<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\Locale;

use Marvin255\DoctrineTranslationBundle\Locale\Locale;
use Marvin255\DoctrineTranslationBundle\Tests\BaseCase;

/**
 * @internal
 */
class LocaleTest extends BaseCase
{
    public function testEmptyConstructorValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Locale('');
    }

    public function testSpacesOnlyConstructorValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Locale('       ');
    }

    /**
     * @dataProvider provideGetLanguage
     */
    public function testGetLanguage(string $locale, string $reference): void
    {
        $locale = new Locale($locale);
        $language = $locale->getLanguage();

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
        $locale = new Locale($locale);
        $region = $locale->getRegion();

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
        $locale = new Locale($locale);
        $full = $locale->getFull();

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

    /**
     * @dataProvider provideEquals
     */
    public function testEquals(string $localeOne, string $localeTwo, bool $reference): void
    {
        $localeObjectOne = new Locale($localeOne);
        $localeObjectTwo = new Locale($localeTwo);

        $result = $localeObjectOne->equals($localeObjectTwo);

        $this->assertSame($reference, $result);
    }

    public function provideEquals(): array
    {
        return [
            'equal' => [
                'en-US',
                'en-US',
                true,
            ],
            'not equal' => [
                'en-US',
                'en-GB',
                false,
            ],
        ];
    }

    /**
     * @dataProvider provideToString
     */
    public function testToString(string $localeString, string $reference): void
    {
        $locale = new Locale($localeString);

        $result = (string) $locale;

        $this->assertSame($reference, $result);
    }

    public function provideToString(): array
    {
        return [
            'full locale' => [
                'en-US',
                'en-US',
            ],
            'short locale' => [
                'en',
                'en',
            ],
        ];
    }
}
