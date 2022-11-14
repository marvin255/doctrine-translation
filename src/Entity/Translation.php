<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Marvin255\DoctrineTranslationBundle\Locale\Locale;
use Marvin255\DoctrineTranslationBundle\Locale\LocaleType;

/**
 * Mapped superclass for doctrine translation entity.
 *
 * @template P of Translatable
 */
#[MappedSuperclass]
abstract class Translation
{
    public const ID_COLUMN_NAME = 'id';
    public const LOCALE_FIELD_NAME = 'locale';
    public const LOCALE_COLUMN_NAME = 'locale';
    public const TRANSLATABLE_FIELD_NAME = 'translatable';
    public const TRANSLATABLE_COLUMN_NAME = 'translatable_id';

    #[Id, GeneratedValue, Column(name: self::ID_COLUMN_NAME, type: Types::INTEGER, nullable: false)]
    protected ?int $id = null;

    #[Column(name: self::LOCALE_COLUMN_NAME, type: LocaleType::LOCALE_TYPE, nullable: false, length: 15)]
    protected ?Locale $locale = null;

    /**
     * @var P|null
     */
    #[ManyToOne(inversedBy: 'translations')]
    #[JoinColumn(name: self::TRANSLATABLE_COLUMN_NAME, nullable: false)]
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
     * @return P|null
     */
    public function getTranslatable(): ?Translatable
    {
        return $this->translatable;
    }

    /**
     * Returns parent object related to this translation.
     *
     * @param P $translatable
     */
    public function setTranslatable(Translatable $translatable): self
    {
        $this->translatable = $translatable;

        return $this;
    }
}
