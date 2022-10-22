<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Locale;

/**
 * Interface for object that represents language locale.
 */
interface Locale
{
    /**
     * Returns primary language from locale. E.g. returns en from en-US.
     *
     * @return string
     */
    public function getLanguage(): string;

    /**
     * Returns region from locale or empty string if there is no region. E.g. returns US from en-US.
     *
     * @return string
     */
    public function getRegion(): string;

    /**
     * Returns full locale name including language and region.
     *
     * @return string
     */
    public function getFull(): string;
}
