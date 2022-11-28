<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\Locale;

use Marvin255\DoctrineTranslationBundle\Locale\LocaleProvider;
use Marvin255\DoctrineTranslationBundle\Tests\BaseCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\Translation\LocaleAwareInterface;

/**
 * @internal
 */
class LocaleProviderTest extends BaseCase
{
    public function testGetCurrentLocale(): void
    {
        $locale = 'en';
        $switcher = $this->createLocaleSwitcherMock($locale);

        $provider = new LocaleProvider($switcher);

        $this->assertSame($locale, $provider->getCurrentLocale()->getFull());
    }

    public function testGetCurrentLocaleEmptyLocale(): void
    {
        $switcher = $this->createLocaleSwitcherMock('');

        $provider = new LocaleProvider($switcher);

        $this->expectException(\RuntimeException::class);
        $provider->getCurrentLocale();
    }

    public function testGetDefaultLocale(): void
    {
        $switcher = $this->createLocaleSwitcherMock();
        $defaultLocale = 'en';

        $provider = new LocaleProvider($switcher, $defaultLocale);

        $this->assertSame($defaultLocale, $provider->getDefaultLocale()->getFull());
    }

    public function testGetDefaultLocaleEmptyLocale(): void
    {
        $switcher = $this->createLocaleSwitcherMock();
        $defaultLocale = '';

        $provider = new LocaleProvider($switcher, $defaultLocale);

        $this->expectException(\RuntimeException::class);
        $provider->getDefaultLocale();
    }

    private function createLocaleSwitcherMock(?string $locale = null): LocaleAwareInterface
    {
        /** @var LocaleAwareInterface&MockObject */
        $localeSwitcher = $this->getMockBuilder(LocaleAwareInterface::class)->getMock();

        if ($locale === null) {
            $localeSwitcher->expects($this->never())->method('getLocale');
        } else {
            $localeSwitcher->method('getLocale')->willReturn($locale);
        }

        return $localeSwitcher;
    }
}
