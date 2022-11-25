<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\StateHandler;

use Doctrine\ORM\EntityManagerInterface;
use Marvin255\DoctrineTranslationBundle\ClassNameManager\ClassNameManager;
use Marvin255\DoctrineTranslationBundle\Entity\Translatable;
use Marvin255\DoctrineTranslationBundle\Entity\Translation;
use Marvin255\DoctrineTranslationBundle\Locale\Locale;
use Marvin255\DoctrineTranslationBundle\Locale\LocaleFactory;
use Symfony\Contracts\Translation\LocaleAwareInterface;

/**
 * Handler object that can persist translation to the storage.
 */
class TranslatableStateHandler
{
    private readonly EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em) 
    {
        $this->em = $em;
    }

    /**
     * Replace all translations for set translatable to the given list of translations and save them.
     * 
     * @psalm-param iterable<Translation> $translations
     */
    public function replaceTranslations(Translatable $translatable, iterable $translations): void
    {
    }
}