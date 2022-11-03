<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Locale;

/**
 * Factory object that can create locale or return one from inner cache since it's totally immutable.
 */
class LocaleFactory
{
    /**
     * @var array<string, Locale>
     */
    private static array $initialized = [];

    /**
     * Creates new locale or pick existed from internal cache.
     */
    public static function create(string $localeName): Locale
    {
        $convertedLocaleName = strtolower(trim($localeName));

        if (!isset(self::$initialized[$convertedLocaleName])) {
            self::$initialized[$convertedLocaleName] = new Locale($localeName);
        }

        return self::$initialized[$convertedLocaleName];
    }
}
