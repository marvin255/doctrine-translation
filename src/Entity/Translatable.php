<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\OneToMany;
use Marvin255\DoctrineTranslationBundle\Locale\Locale;

/**
 * Interface for item that can have translations.
 *
 * @psalm-template R of Translation
 */
#[MappedSuperclass]
abstract class Translatable
{
    /**
     * @psalm-var Collection<int, R>
     */
    #[OneToMany(targetEntity: Translation::class, mappedBy: 'translatable', orphanRemoval: true)]
    protected Collection $translations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    /**
     * Returns collection of all translations for this item.
     *
     * @psalm-return R[]
     */
    public function getTranslations(): array
    {
        return $this->translations->toArray();
    }

    /**
     * Removes all related translation from current object.
     */
    public function clearTranslations(): self
    {
        $this->translations->clear();

        return $this;
    }

    /**
     * Add new translation.
     *
     * @psalm-param R $translation
     */
    public function addTranslation(Translation $translation): self
    {
        $this->translations->add($translation);

        return $this;
    }

    /**
     * Remove translation.
     *
     * @psalm-param R $translation
     */
    public function removeTranslation(Translation $translation): self
    {
        $this->translations->removeElement($translation);

        return $this;
    }

    /**
     * Search and return translation for set locale.
     *
     * @psalm-return R|null
     */
    public function findTranslationByLocale(Locale $locale): ?Translation
    {
        $item = $this->translations
            ->filter(fn (Translation $t): bool => $t->getLocale()?->equals($locale) === true)
            ->first();

        return $item === false ? null : $item;
    }
}
