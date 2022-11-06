<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Marvin255\DoctrineTranslationBundle\ClassNameManager\ClassNameManager;
use Marvin255\DoctrineTranslationBundle\Entity\Translatable;
use Marvin255\DoctrineTranslationBundle\Entity\Translation;
use Marvin255\DoctrineTranslationBundle\Locale\Locale;
use Marvin255\DoctrineTranslationBundle\Locale\LocaleFactory;
use Symfony\Component\Translation\LocaleSwitcher;

/**
 * Repository that can query translations for items.
 */
class TranslationRepository
{
    private readonly EntityManagerInterface $em;

    private readonly LocaleSwitcher $localeSwitcher;

    private readonly ClassNameManager $classNameManager;

    public function __construct(
        EntityManagerInterface $em,
        LocaleSwitcher $localeSwitcher,
        ClassNameManager $classNameManager
    ) {
        $this->em = $em;
        $this->localeSwitcher = $localeSwitcher;
        $this->classNameManager = $classNameManager;
    }

    /**
     * Searches translations related for set list of items and current app locale.
     *
     * @param iterable<Translatable>|Translatable $items
     *
     * @return iterable<Translation>
     */
    public function findTranslationForCurrentLocale(iterable|Translatable $items): iterable
    {
        $currentLocale = LocaleFactory::create($this->localeSwitcher->getLocale());

        return $this->findTranslations($items, $currentLocale);
    }

    /**
     * Searches translations related for set list of items.
     * If locales set then translations will be load only for that locales.
     *
     * @param iterable<Translatable>|Translatable $items
     * @param iterable<Locale>|Locale             $locales
     *
     * @return iterable<Translation>
     */
    public function findTranslations(iterable|Translatable $items, iterable|Locale $locales = []): iterable
    {
        $itemsByClasses = $this->groupItemsByTranslationClass($items);

        if (empty($itemsByClasses)) {
            return [];
        }

        $localesStrings = $this->getLocaleStringsFromLocales($locales);

        $result = [];
        $alias = 't';
        foreach ($itemsByClasses as $translationClass => $translatableItems) {
            $qb = $this->em->createQueryBuilder();
            $qb->select($alias)->from($translationClass, $alias);
            $qb->where("{$alias}." . Translation::TRANSLATABLE_FIELD_NAME . ' IN (:translatables)');
            $qb->setParameter('translatables', $translatableItems);
            if (!empty($localesStrings)) {
                $qb->andWhere("{$alias}." . Translation::LOCALE_FIELD_NAME . ' IN (:locales)');
                $qb->setParameter('locales', $localesStrings);
            }
            /** @var Translation[] */
            $tmpResult = $qb->getQuery()->getResult();
            $result = array_merge($result, $tmpResult);
        }

        return $result;
    }

    /**
     * Groups translatable items by translation class for search.
     *
     * @param iterable<Translatable>|Translatable $items
     *
     * @return array<string, Translatable[]>
     */
    private function groupItemsByTranslationClass(iterable|Translatable $items): array
    {
        if ($items instanceof Translatable) {
            $items = [$items];
        }

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
     * @param iterable<Locale>|Locale $locales
     *
     * @return string[]
     */
    private function getLocaleStringsFromLocales(iterable|Locale $locales): array
    {
        if ($locales instanceof Locale) {
            $locales = [$locales];
        }

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
