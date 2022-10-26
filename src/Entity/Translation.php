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
 * @psalm-template T of Translatable
 */
#[MappedSuperclass]
abstract class Translation
{
    #[Id, Column(name: 'id', type: 'integer')]
    protected ?int $id = null;

    #[Column(name: 'locale', type: LocaleType::LOCALE_TYPE, nullable: false)]
    protected ?Locale $locale = null;

    /**
     * @psalm-var T|null
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
     * @psalm-return T|null
     */
    public function getTranslatable(): ?Translatable
    {
        return $this->translatable;
    }

    /*
     * @qqqqq-psalm-param T $translatable
     */
    /*public function setTranslatable(int): self
    {
        // $this->translatable = $qwe;

        return $this;
    }*/
}
