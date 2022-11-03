<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\Locale;

use Marvin255\DoctrineTranslationBundle\Locale\LocaleFactory;
use Marvin255\DoctrineTranslationBundle\Tests\BaseCase;

/**
 * @internal
 */
class LocaleFactoryTest extends BaseCase
{
    public function testCreateNew(): void
    {
        $localeString = 'en-US';

        $locale = LocaleFactory::create($localeString);

        $this->assertSame($localeString, $locale->getFull());
    }

    public function testCreateCache(): void
    {
        $localeString = '     en-US';
        $localeString1 = 'en-us    ';

        $locale = LocaleFactory::create($localeString);
        $locale1 = LocaleFactory::create($localeString1);

        $this->assertSame($locale, $locale1);
    }
}
