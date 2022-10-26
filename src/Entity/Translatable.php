<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Marvin255\DoctrineTranslationBundle\Locale\Locale;
use Marvin255\DoctrineTranslationBundle\Translation\Translation;

/**
 * Interface for item that can have translations.
 *
 * @psalm-template T of Translation
 */
interface Translatable
{
    /*
     * Returns collection of all translations for this item.
     *
     * @pslam-return Collection<int, T>
     */
    // public function getTranslations(): Collection;

    /*
     * Search and return translation for set locale.
     *
     * @pslam-return T|null
     */
    // public function findTranslation(Locale $locale): ?Translation;

    /*
     * Add new translation.
     *
     * @pslam-param T $translation
     */
    // public function addTranslation(Translation $translation): self;

    /*
     * Remove translation.
     *
     * @pslam-param T $translation
     */
    // public function removeTranslation(Translation $translation): self;
}
