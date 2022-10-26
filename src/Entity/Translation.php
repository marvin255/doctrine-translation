<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Marvin255\DoctrineTranslationBundle\Locale\Locale;
use Marvin255\DoctrineTranslationBundle\Locale\LocaleType;

/**
 * Mapped superclass for doctrine translation entity.
 *
 * @psalm-template P of Translatable
 */
#[MappedSuperclass]
abstract class Translation
{
    #[Id, Column(name: 'id', type: 'integer')]
    protected ?int $id = null;

    #[Column(name: 'locale', type: LocaleType::LOCALE_TYPE, nullable: false)]
    protected ?Locale $locale = null;

    /**
     * @psalm-var P|null
     */
    #[ManyToOne(inversedBy: 'translations')]
    #[JoinColumn(name: 'translatable_id', nullable: false)]
    protected ?Translatable $translatable = null;

    /**
     * Returns primary key for this translation.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Returns locale related to this translation.
     */
    public function getLocale(): ?Locale
    {
        return $this->locale;
    }

    /**
     * Sets locale for this translation.
     */
    public function setLocale(Locale $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Returns parent object for this translation.
     *
     * @psalm-return P|null
     */
    public function getTranslatable(): ?Translatable
    {
        return $this->translatable;
    }

    /**
     * Returns parent object related to this translation.
     *
     * @psalm-param P $translatable
     */
    public function setTranslatable(Translatable $translatable): self
    {
        $this->translatable = $translatable;

        return $this;
    }
}
