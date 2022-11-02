<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Locale;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\StringType;

/**
 * Doctrine type field to save and load locale.
 */
class LocaleType extends StringType
{
    public const LOCALE_TYPE = 'marvin255_locale';

    /**
     * {@inheritDoc}
     *
     * @return Locale|null
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?Locale
    {
        if ($value === null) {
            return null;
        }

        if (\is_string($value)) {
            try {
                $locale = new Locale($value);
            } catch (\Throwable $e) {
                throw ConversionException::conversionFailedFormat($value, self::LOCALE_TYPE, 'en-US', $e);
            }

            return $locale;
        }

        throw ConversionException::conversionFailed($value, self::LOCALE_TYPE);
    }

    /**
     * {@inheritDoc}
     *
     * @return string|null
     */
    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
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
