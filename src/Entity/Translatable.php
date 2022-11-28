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
    public const TRANSLATIONS_FIELD_NAME = 'translations';
    public const TRANSLATED_FIELD_NAME = 'translated';

    /**
     * @psalm-var Collection<int, R>
     */
    #[OneToMany(targetEntity: Translation::class, mappedBy: 'translatable', orphanRemoval: true)]
    protected Collection $translations;

    /**
     * @psalm-var R|null
     */
    private ?Translation $translated = null;

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
        if (!$this->translations->contains($translation)) {
            $translation->setTranslatable($this);
            $this->translations->add($translation);
        }

        return $this;
    }

    /**
     * Remove translation.
     *
     * @psalm-param R $translation
     */
    public function removeTranslation(Translation $translation): self
    {
        if ($this->translations->contains($translation)) {
            $translation->setTranslatable(null);
            $this->translations->removeElement($translation);
        }

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

    /**
     * This field can be used to provide external translation and avoid loading list of all related translations.
     * E.g. load translations for whole list of items using just one query and set correct translation for each item.
     *
     * @psalm-param R|null $translated
     */
    public function setTranslated(?Translation $translated): self
    {
        $this->translated = $translated;

        return $this;
    }

    /**
     * Returns provided translation.
     *
     * @psalm-return R|null
     */
    public function getTranslated(): ?Translation
    {
        return $this->translated;
    }
}
