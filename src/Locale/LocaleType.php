<?php

declare(strict_types=1);

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\StringType;
use Marvin255\DoctrineTranslation\Locale\Locale;
use Marvin255\DoctrineTranslation\Locale\LocaleValue;

/**
 * Doctrine type field to save and load locale.
 */
class LocaleType extends StringType
{
    public const LOCALE_TYPE = 'marvin255_locale';

    /**
     * {@inheritDoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        if (!is_string($value)) {
            throw ConversionException::conversionFailed($value, self::LOCALE_TYPE);
        }

        try {
            $locale = new LocaleValue($value);
        } catch (Throwable $e) {
            throw ConversionException::conversionFailed($value, self::LOCALE_TYPE, $e);
        }

        return $locale;
    }

    /**
     * {@inheritDoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        if (!($value instanceof Locale)) {
            throw ConversionException::conversionFailed($value, self::LOCALE_TYPE);
        }

        return $value->getFull();
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return self::LOCALE_TYPE;
    }
}
