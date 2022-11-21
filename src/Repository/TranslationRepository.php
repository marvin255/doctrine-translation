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
    public const QUERY_ALIAS = 't';

    private readonly EntityManagerInterface $em;

    private readonly LocaleSwitcher $localeSwitcher;

    private readonly ClassNameManager $classNameManager;

    private readonly EntityComparator $comparator;

    public function __construct(
        EntityManagerInterface $em,
        LocaleSwitcher $localeSwitcher,
        ClassNameManager $classNameManager,
        ?EntityComparator $comparator = null
    ) {
        $this->em = $em;
        $this->localeSwitcher = $localeSwitcher;
        $this->classNameManager = $classNameManager;
        $this->comparator = $comparator ?: new EntityComparator($em);
    }

    /**
     * Searches and sets translations related for set list of items and current app locale.
     *
     * @param iterable<Translatable>|Translatable $items
     */
    public function findAndSetTranslationForCurrentLocale(iterable|Translatable $items): void
    {
        $this->setItemsTranslated(
            $items,
            $this->findTranslationForCurrentLocale($items)
        );
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
     * Searches and sets translations related for set list of items and set locale.
     *
     * @param iterable<Translatable>|Translatable $items
     * @param Locale                              $locale
     */
    public function findAndSetTranslationForLocale(iterable|Translatable $items, Locale $locale): void
    {
        $this->setItemsTranslated(
            $items,
            $this->findTranslations($items, $locale)
        );
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
        foreach ($itemsByClasses as $translationClass => $translatableItems) {
            $qb = $this->em->createQueryBuilder();
            $qb->select(self::QUERY_ALIAS);
            $qb->from($translationClass, self::QUERY_ALIAS);
            $qb->where(self::QUERY_ALIAS . '.' . Translation::TRANSLATABLE_FIELD_NAME . ' IN (:translatables)');
            $qb->setParameter('translatables', $translatableItems);
            if (!empty($localesStrings)) {
                $qb->andWhere(self::QUERY_ALIAS . '.' . Translation::LOCALE_FIELD_NAME . ' IN (:locales)');
                $qb->setParameter('locales', $localesStrings);
            }
            /** @var Translation[] */
            $tmpResult = $qb->getQuery()->getResult();
            $result = array_merge($result, $tmpResult);
        }

        return $result;
    }

    /**
     * Uses list of translations to set current translations for all translatable items.
     *
     * @param iterable<Translatable>|Translatable $items
     * @param iterable<Translation>|Translation   $translations
     */
    public function setItemsTranslated(iterable|Translatable $items, iterable|Translation $translations): void
    {
        $items = $items instanceof Translatable ? [$items] : $items;
        $translations = $translations instanceof Translation ? [$translations] : $translations;

        foreach ($items as $item) {
            $translated = [];
            foreach ($translations as $translation) {
                $parentTranslatable = $translation->getTranslatable();
                if ($parentTranslatable !== null && $this->comparator->isEqual($item, $parentTranslatable)) {
                    $translated[] = $translation;
                }
            }
            $item->setTranslated($translated);
        }
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
        $items = $items instanceof Translatable ? [$items] : $items;

        $itemsByClasses = [];
        foreach ($items as $item) {
            $translationClass = $this->classNameManager->getTranslationClassForTranslatableEntity($item);
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
        $locales = $locales instanceof Locale ? [$locales] : $locales;

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
