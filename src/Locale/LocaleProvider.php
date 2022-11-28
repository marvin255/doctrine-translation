<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Locale;

use Symfony\Contracts\Translation\LocaleAwareInterface;

/**
 * Object that can provide current locale and default locale from Symfony settings.
 */
class LocaleProvider
{
    private readonly LocaleAwareInterface $localeSwitcher;

    private readonly string $defaultLocaleString;

    private ?Locale $defaultLocale = null;

    public function __construct(LocaleAwareInterface $localeSwitcher, ?string $defaultLocaleString = null)
    {
        $this->localeSwitcher = $localeSwitcher;
        $this->defaultLocaleString = $defaultLocaleString ?? '';
    }

    /**
     * Return object that represents current locale.
     */
    public function getCurrentLocale(): Locale
    {
        $currentLocaleString = $this->localeSwitcher->getLocale();

        if ($currentLocaleString === '') {
            throw new \RuntimeException("Current locale wasn't provided");
        }

        return LocaleFactory::create($currentLocaleString);
    }

    /**
     * Return object that represents default locale.
     */
    public function getDefaultLocale(): Locale
    {
        if ($this->defaultLocaleString === '') {
            throw new \RuntimeException("Default locale wasn't provided");
        }

        if ($this->defaultLocale === null) {
            $this->defaultLocale = LocaleFactory::create($this->defaultLocaleString);
        }

        return $this->defaultLocale;
    }
}
