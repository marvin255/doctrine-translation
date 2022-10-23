<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Translation;

use Marvin255\DoctrineTranslationBundle\Locale\Locale;

/**
 * Interface for translation item.
 */
interface Translation
{
    /**
     * Returns locale for this translation.
     *
     * @return Locale
     */
    public function getLocale(): Locale;
}
