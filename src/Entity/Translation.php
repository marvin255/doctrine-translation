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
 */
#[MappedSuperclass]
abstract class Translation
{
    #[Id, Column(name: 'id', type: 'integer')]
    protected ?int $id = null;

    #[Column(name: 'locale', type: LocaleType::LOCALE_TYPE, nullable: false)]
    protected ?Locale $locale = null;

    #[ManyToOne(inversedBy: 'translations')]
    #[JoinColumn(name: 'translatable_id', nullable: false)]
    protected ?Translatable $translatable = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLocale(): ?Locale
    {
        return $this->locale;
    }

    public function setLocale(Locale $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    /*public function getTranslatable(): ?Translatable
    {
        return $this->translatable;
    }

    public function setTranslatable(Translatable $translatable): self
    {
        $this->translatable = $translatable;

        return $this;
    }*/
}
