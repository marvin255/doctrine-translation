<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\StateHandler;

use Marvin255\DoctrineTranslationBundle\ClassNameManager\ClassNameManager;
use Marvin255\DoctrineTranslationBundle\Entity\Translatable;
use Marvin255\DoctrineTranslationBundle\Entity\Translation;
use Marvin255\DoctrineTranslationBundle\EntityManager\EntityManagerProvider;

/**
 * Handler object that can persist translation to the storage.
 */
class TranslatableStateHandler
{
    private readonly EntityManagerProvider $emProvider;

    private readonly ClassNameManager $classNameManager;

    public function __construct(
        EntityManagerProvider $emProvider,
        ClassNameManager $classNameManager
    ) {
        $this->emProvider = $emProvider;
        $this->classNameManager = $classNameManager;
    }

    /**
     * Replace all translations for set translatable to the given list of translations and save them.
     *
     * @psalm-param iterable<Translation> $translations
     */
    public function replaceTranslations(Translatable $translatable, iterable $translations): void
    {
        $translationClass = $this->classNameManager->getTranslationClassForTranslatableEntity($translatable);
        $em = $this->emProvider->getEntityManagerForClass($translationClass);

        $itemsToChange = [];
        foreach ($translations as $translation) {
            $locale = $translation->getLocale();
            $relatedTranslation = $locale ? $translatable->findTranslationByLocale($locale) : null;
            if ($relatedTranslation === null) {
                $em->persist($translation);
                $translatable->addTranslation($translation);
                $itemsToChange[] = $translation;
            } else {
                $this->transferContentDataBetweenTranslations($translation, $relatedTranslation);
                $itemsToChange[] = $relatedTranslation;
            }
        }

        foreach ($translatable->getTranslations() as $translation) {
            if (!\in_array($translation, $itemsToChange, true)) {
                $em->remove($translation);
                $translatable->removeTranslation($translation);
            }
        }

        $em->flush();
    }

    /**
     * Transfer fields from second translation to the first one.
     */
    private function transferContentDataBetweenTranslations(Translation $from, Translation $to): void
    {
        $meta = $this->emProvider->getClassMetadataForEntity($from);
        $readonlyFields = [
            Translation::ID_COLUMN_NAME,
            Translation::LOCALE_FIELD_NAME,
            Translation::TRANSLATABLE_FIELD_NAME,
        ];

        foreach ($meta->getFieldNames() as $fieldName) {
            if (\in_array($fieldName, $readonlyFields, true)) {
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
