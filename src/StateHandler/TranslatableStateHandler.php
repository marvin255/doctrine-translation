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
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use InvalidArgumentException;

/**
 * Handler object that can persist translation to the storage.
 */
class TranslatableStateHandler
{
    private readonly EntityManagerInterface $em;

    private readonly ClassNameManager $classNameManager;

    public function __construct(
        EntityManagerInterface $em,
        ClassNameManager $classNameManager
    ) {
        $this->em = $em;
        $this->classNameManager = $classNameManager;
    }

    /**
     * Replace all translations for set translatable to the given list of translations and save them.
     * 
     * @psalm-param iterable<Translation> $translations
     */
    public function replaceTranslations(Translatable $translatable, iterable $translations): void
    {
        $this->transferContentDataBetweenTranslations($translations[0], $translations[1]);
        $translationClass = $this->classNameManager->getTranslationClassForTranslatableEntity($translatable);

        $toUpdateItems = [];
        foreach ($translations as $translation) {
            if (!is_a($translation, $translationClass)) {
                throw new InvalidArgumentException("All translations must implement '{$translationClass}'");
            }
            $translationLocale = $translation->getLocale();
            $relatedTranslation = $translationLocale ? $translatable->findTranslationByLocale($translationLocale) : null;
            if ($relatedTranslation === null) {
                $this->em->persist($translation);
                $translatable->addTranslation($translation);
            } else {
                $this->transferContentDataBetweenTranslations($translation, $relatedTranslation);
                $toUpdateItems[] = $relatedTranslation;
            }
        }

        foreach ($translatable->getTranslations() as $translation) {
            if (!in_array($translation, $toUpdateItems, true)) {
                $this->em->remove($translation);
                $translatable->removeTranslation($translation);
            }
        }

        $this->em->flush();
    }

    /**
     * Transfer fields from second translation to the first one.
     */
    private function transferContentDataBetweenTranslations(Translation $from, Translation $to): void
    {
        $meta = $this->em->getClassMetadata(get_class($from));
        $readonlyFields = [
            Translation::ID_COLUMN_NAME,
            Translation::LOCALE_FIELD_NAME,
            Translation::TRANSLATABLE_FIELD_NAME,
        ];

        foreach ($meta->getFieldNames() as $fieldName) {
            if (in_array($fieldName, $readonlyFields, true)) {
                continue;
            }
            $meta->setFieldValue(
                $to, 
                $fieldName,
                $meta->getFieldValue($from, $fieldName)
            );
        }
    }
}