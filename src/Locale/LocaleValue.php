<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslation\Locale;

use InvalidArgumentException;

/**
 * Simple object that contains locale string and profides Locale interface.
 */
final class LocaleValue implements Locale
{
    private readonly string $language;

    private readonly string $region;

    private readonly string $full;

    public function __construct(string $locale)
    {
        $trimmedLocale = trim($locale);
        if ($trimmedLocale === '') {
            throw new InvalidArgumentException("Locale can't be an empty string");
        }

        $arLocale = explode('-', $trimmedLocale);

        $this->language = strtolower(trim($arLocale[0] ?? ''));
        $this->region = strtoupper(trim($arLocale[1] ?? ''));
        $this->full = $this->language . ($this->region === '' ? '' : "-{$this->region}");
    }

    /**
     * {@inheritDoc}
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * {@inheritDoc}
     */
    public function getRegion(): string
    {
        return $this->region;
    }

    /**
     * {@inheritDoc}
     */
    public function getFull(): string
    {
        return $this->full;
    }
}
