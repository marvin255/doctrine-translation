<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Repository;

use Doctrine\ORM\EntityManager;
use Marvin255\DoctrineTranslationBundle\ClassNameManager\ClassNameManager;
use Marvin255\DoctrineTranslationBundle\Entity\Translatable;
use Marvin255\DoctrineTranslationBundle\Entity\Translation;
use Marvin255\DoctrineTranslationBundle\Locale\Locale;

/**
 * Repository that can query translations for items.
 */
class TranslationRepository
{
    private readonly EntityManager $em;

    private readonly ClassNameManager $classNameManager;

    public function __construct(EntityManager $em, ClassNameManager $classNameManager)
    {
        $this->em = $em;
        $this->classNameManager = $classNameManager;
    }

    /**
     * Searches translations related for set item.
     * If locales set then translations will be load only for that locales.
     *
     * @param Translatable     $item
     * @param iterable<Locale> $locales
     *
     * @return iterable<Translation>
     */
    public function findTranslationsForItem(Translatable $item, iterable $locales = []): iterable
    {
        return $this->findTranslationsForItems([$item], $locales);
    }

    /**
     * Searches translations related for set list of items.
     * If locales set then translations will be load only for that locales.
     *
     * @param iterable<Translatable> $items
     * @param iterable<Locale>       $locales
     *
     * @return iterable<Translation>
     */
    public function findTranslationsForItems(iterable $items, iterable $locales = []): iterable
    {
        $itemsByClasses = $this->groupItemsByTranslationClass($items);

        if (empty($itemsByClasses)) {
            return [];
        }

        $localesStrings = $this->getLocaleStringsFromLocales($locales);

        $result = [];
        foreach ($itemsByClasses as $translationClass => $translatableItems) {
            $qb = $this->em->createQueryBuilder();
            $qb->from($translationClass, 't');
            $qb->where('t.' . Translation::TRANSLATABLE_FIELD_NAME . ' IN (:translatables)');
            $qb->setParameter('translatables', $translatableItems);
            if (!empty($localesStrings)) {
                $qb->andWhere('t.' . Translation::LOCALE_FIELD_NAME . ' IN (:locales)');
                $qb->setParameter('locales', $localesStrings);
            }
            /** @var Translation[] */
            $tmpResult = $qb->getQuery()->getArrayResult();
            $result = array_merge($result, $tmpResult);
        }

        return $result;
    }

    /**
     * Groups translatable items by translation class for search.
     *
     * @param iterable<Translatable> $items
     *
     * @return array<string, Translatable[]>
     */
    private function groupItemsByTranslationClass(iterable $items): array
    {
        $itemsByClasses = [];
        foreach ($items as $item) {
            $itemClass = \get_class($item);
            $translationClass = $this->classNameManager->getTranslationClassForTranslatable($itemClass);
            $itemsByClasses[$translationClass][] = $item;
        }

        return $itemsByClasses;
    }

    /**
     * Convert list of Locale objects to list of strings.
     *
     * @param iterable<Locale> $locales
     *
     * @return string[]
     */
    private function getLocaleStringsFromLocales(iterable $locales): array
    {
        $localesStrings = [];
        foreach ($locales as $locale) {
            $localeString = $locale->getFull();
            if (!\in_array($localeString, $localesStrings)) {
                $localesStrings[] = $localeString;
            }
        }

        return $localesStrings;
    }
}
