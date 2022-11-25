<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\Repository;

use Marvin255\DoctrineTranslationBundle\Entity\Translation;
use Marvin255\DoctrineTranslationBundle\Repository\EntityComparator;
use Marvin255\DoctrineTranslationBundle\Repository\TranslationRepository;
use Marvin255\DoctrineTranslationBundle\Tests\EmCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\Translation\LocaleAwareInterface;

/**
 * @internal
 */
class TranslationRepositoryTest extends EmCase
{
    public const TRANSLATABLES_WHERE = TranslationRepository::QUERY_ALIAS . '.' . Translation::TRANSLATABLE_FIELD_NAME . ' IN (:translatables)';
    public const LOCALES_WHERE = [[TranslationRepository::QUERY_ALIAS . '.' . Translation::LOCALE_FIELD_NAME . ' IN (:locales)']];

    public function testFindAndSetTranslationForCurrentLocale(): void
    {
        $translationParent = $this->createTranslatableMock();
        $translation = $this->createTranslationMock($translationParent);
        $translatable = $this->createTranslatableMock($translation);

        $qb = $this->createQueryBuilderMock(
            [
                'select' => TranslationRepository::QUERY_ALIAS,
                'from' => [self::BASE_TRANSLATION_CLASS, TranslationRepository::QUERY_ALIAS],
                'where' => self::TRANSLATABLES_WHERE,
                'andWhere' => self::LOCALES_WHERE,
                'setParameter' => [
                    ['translatables', [$translatable]],
                    ['locales', self::BASE_LOCALE_ARRAY],
                ],
            ],
            [$translation]
        );

        $em = $this->createEmMock([], $qb);
        $localeSwitcher = $this->createLocaleSwitcherMock();
        $classNameManager = $this->createBasicClassNameManagerMock($translatable, $translation);
        $comparator = $this->createEntityComparatorMock($translationParent, $translatable);

        $repo = new TranslationRepository($em, $localeSwitcher, $classNameManager, $comparator);
        $repo->findAndSetTranslationForCurrentLocale($translatable);
    }

    public function testFindAndSetTranslationForCurrentLocaleDefaulLocaleFallback(): void
    {
        $defaultLocaleStr = 'fr';
        $defaultLocale = $this->createLocaleMock($defaultLocaleStr);

        $localeStr = 'en';
        $locale = $this->createLocaleMock($localeStr);

        $translationParent = $this->createTranslatableMock();
        $translationDefault = $this->createTranslationMock($translationParent, $defaultLocale);
        $translation = $this->createTranslationMock($translationParent, $locale);
        $translatable = $this->createTranslatableMock($translation);

        $translationParentFallback = $this->createTranslatableMock();
        $translationFallback = $this->createTranslationMock($translationParentFallback, $defaultLocale);
        $translatableFallback = $this->createTranslatableMock($translationFallback);

        $qb = $this->createQueryBuilderMock(
            [
                'select' => TranslationRepository::QUERY_ALIAS,
                'from' => [self::BASE_TRANSLATION_CLASS, TranslationRepository::QUERY_ALIAS],
                'where' => self::TRANSLATABLES_WHERE,
                'andWhere' => self::LOCALES_WHERE,
                'setParameter' => [
                    ['translatables', [$translatable, $translatableFallback]],
                    ['locales', [$localeStr, $defaultLocaleStr]],
                ],
            ],
            [$translationDefault, $translation, $translationFallback]
        );

        $em = $this->createEmMock([], $qb);
        $localeSwitcher = $this->createLocaleSwitcherMock($localeStr);
        $classNameManager = $this->createBasicClassNameManagerMock($translatable, $translation);
        $classNameManager = $this->createClassNameManagerMock(
            self::BASE_CLASS_NAMES_MAP,
            [
                self::BASE_TRANSLATABLE_CLASS => [$translatable, $translatableFallback],
            ]
        );
        $comparator = $this->createEntityComparatorMock([
            [$translationParent, $translatable],
            [$translationParentFallback, $translatableFallback],
        ]);

        $repo = new TranslationRepository($em, $localeSwitcher, $classNameManager, $comparator, $defaultLocaleStr);
        $repo->findAndSetTranslationForCurrentLocale([$translatable, $translatableFallback]);
    }

    public function testFindTranslationForCurrentLocale(): void
    {
        $translatable = $this->createTranslatableMock();
        $translatable1 = $this->createTranslatableMock();
        $translatables = [$translatable, $translatable1];
        $reference = [
            $this->createTranslationMock(),
            $this->createTranslationMock(),
        ];

        $qb = $this->createQueryBuilderMock(
            [
                'select' => TranslationRepository::QUERY_ALIAS,
                'from' => [self::BASE_TRANSLATION_CLASS, TranslationRepository::QUERY_ALIAS],
                'where' => self::TRANSLATABLES_WHERE,
                'andWhere' => self::LOCALES_WHERE,
                'setParameter' => [
                    ['translatables', $translatables],
                    ['locales', self::BASE_LOCALE_ARRAY],
                ],
            ],
            $reference
        );

        $em = $this->createEmMock([], $qb);
        $localeSwitcher = $this->createLocaleSwitcherMock();
        $classNameManager = $this->createClassNameManagerMock(
            self::BASE_CLASS_NAMES_MAP,
            [
                self::BASE_TRANSLATABLE_CLASS => $translatables,
            ]
        );

        $repo = new TranslationRepository($em, $localeSwitcher, $classNameManager);
        $res = $repo->findTranslationForCurrentLocale($translatables);

        $this->assertSame($reference, $res);
    }

    public function testFindAndSetTranslationForLocale(): void
    {
        $translationParent = $this->createTranslatableMock();
        $translation = $this->createTranslationMock($translationParent);
        $translatable = $this->createTranslatableMock($translation);
        $locale = $this->createLocaleMock();

        $qb = $this->createQueryBuilderMock(
            [
                'select' => TranslationRepository::QUERY_ALIAS,
                'from' => [self::BASE_TRANSLATION_CLASS, TranslationRepository::QUERY_ALIAS],
                'where' => self::TRANSLATABLES_WHERE,
                'andWhere' => self::LOCALES_WHERE,
                'setParameter' => [
                    ['translatables', [$translatable]],
                    ['locales', self::BASE_LOCALE_ARRAY],
                ],
            ],
            [$translation]
        );

        $em = $this->createEmMock([], $qb);
        $localeSwitcher = $this->createLocaleSwitcherMock();
        $classNameManager = $this->createBasicClassNameManagerMock($translatable, $translation);
        $comparator = $this->createEntityComparatorMock($translationParent, $translatable);

        $repo = new TranslationRepository($em, $localeSwitcher, $classNameManager, $comparator);
        $repo->findAndSetTranslationForLocale($translatable, $locale);
    }

    public function testFindTranslations(): void
    {
        $translationClass = 'translation';
        $translationClass1 = 'translation1';
        $translatable = $this->createTranslatableMock();
        $translatable1 = $this->createTranslatableMock();
        $translatable2 = $this->createTranslatableMock();

        $localeString = 'en-US';
        $localeString1 = 'en-GB';
        $locale = $this->createLocaleMock($localeString);
        $locale1 = $this->createLocaleMock($localeString1);

        $reference = [
            $this->createTranslationMock(),
            $this->createTranslationMock(),
        ];

        $qb = $this->createQueryBuilderMock(
            [
                'select' => TranslationRepository::QUERY_ALIAS,
                'from' => [$translationClass, TranslationRepository::QUERY_ALIAS],
                'where' => self::TRANSLATABLES_WHERE,
                'andWhere' => self::LOCALES_WHERE,
                'setParameter' => [
                    ['translatables', [$translatable, $translatable1]],
                    ['locales', [$localeString, $localeString1]],
                ],
            ],
            [$reference[0]]
        );

        $qb1 = $this->createQueryBuilderMock(
            [
                'select' => TranslationRepository::QUERY_ALIAS,
                'from' => [$translationClass1, TranslationRepository::QUERY_ALIAS],
                'where' => self::TRANSLATABLES_WHERE,
                'andWhere' => self::LOCALES_WHERE,
                'setParameter' => [
                    ['translatables', [$translatable2]],
                    ['locales', [$localeString, $localeString1]],
                ],
            ],
            [$reference[1]]
        );

        $em = $this->createEmMock([], [$qb, $qb1]);
        $localeSwitcher = $this->createLocaleSwitcherMock();
        $classNameManager = $this->createClassNameManagerMock(
            [
                'translatable' => $translationClass,
                'translatable2' => $translationClass1,
            ],
            [
                'translatable' => [$translatable, $translatable1],
                'translatable2' => $translatable2,
            ]
        );

        $repo = new TranslationRepository($em, $localeSwitcher, $classNameManager);
        $res = $repo->findTranslations(
            [
                $translatable,
                $translatable1,
                $translatable2,
            ],
            [
                $locale,
                $locale1,
            ]
        );

        $this->assertSame($reference, $res);
    }

    public function testFindTranslationsNoLocale(): void
    {
        $translatable = $this->createTranslatableMock();
        $reference = [
            $this->createTranslationMock(),
            $this->createTranslationMock(),
        ];

        $qb = $this->createQueryBuilderMock(
            [
                'select' => TranslationRepository::QUERY_ALIAS,
                'from' => [self::BASE_TRANSLATION_CLASS, TranslationRepository::QUERY_ALIAS],
                'where' => self::TRANSLATABLES_WHERE,
                'setParameter' => [
                    ['translatables', [$translatable]],
                ],
            ],
            $reference
        );

        $em = $this->createEmMock([], $qb);
        $localeSwitcher = $this->createLocaleSwitcherMock();
        $classNameManager = $this->createBasicClassNameManagerMock($translatable);

        $repo = new TranslationRepository($em, $localeSwitcher, $classNameManager);
        $res = $repo->findTranslations([$translatable]);

        $this->assertSame($reference, $res);
    }

    public function testFindTranslationsNoLocaleSingleItem(): void
    {
        $translatable = $this->createTranslatableMock();
        $reference = [
            $this->createTranslationMock(),
            $this->createTranslationMock(),
        ];

        $qb = $this->createQueryBuilderMock(
            [
                'select' => TranslationRepository::QUERY_ALIAS,
                'from' => [self::BASE_TRANSLATION_CLASS, TranslationRepository::QUERY_ALIAS],
                'where' => self::TRANSLATABLES_WHERE,
                'setParameter' => [
                    ['translatables', [$translatable]],
                ],
            ],
            $reference
        );

        $em = $this->createEmMock([], $qb);
        $localeSwitcher = $this->createLocaleSwitcherMock();
        $classNameManager = $this->createBasicClassNameManagerMock($translatable);

        $repo = new TranslationRepository($em, $localeSwitcher, $classNameManager);
        $res = $repo->findTranslations($translatable);

        $this->assertSame($reference, $res);
    }

    public function testFindTranslationsEmptyTranslatableList(): void
    {
        $em = $this->createEmMock();
        $localeSwitcher = $this->createLocaleSwitcherMock();
        $classNameManager = $this->createClassNameManagerMock();

        $repo = new TranslationRepository($em, $localeSwitcher, $classNameManager);
        $res = $repo->findTranslations([]);

        $this->assertSame([], $res);
    }

    public function testSetItemsTranslated(): void
    {
        $translationEqualParent = $this->createTranslatableMock();
        $translationEqual = $this->createTranslationMock($translationEqualParent);

        $translationEqualParentSecond = $this->createTranslatableMock();
        $translationEqualSecond = $this->createTranslationMock($translationEqualParentSecond);

        $translatableEqual = $this->createTranslatableMock($translationEqual);

        $translationNonEqual = $this->createTranslationMock();
        $translatableNonEqual = $this->createTranslatableMock(null);

        $em = $this->createEmMock();
        $localeSwitcher = $this->createLocaleSwitcherMock();
        $classNameManager = $this->createClassNameManagerMock();
        $comparator = $this->createEntityComparatorMock(
            [
                [$translationEqualParent, $translatableEqual],
                [$translationEqualParentSecond, $translatableEqual],
            ]
        );

        $repo = new TranslationRepository($em, $localeSwitcher, $classNameManager, $comparator);
        $repo->setItemsTranslated(
            [
                $translatableEqual,
                $translatableNonEqual,
            ],
            [
                $translationEqual,
                $translationNonEqual,
                $translationEqualSecond,
            ]
        );
    }

    public function testSetItemsTranslatedFallbackLocale(): void
    {
        $locale = $this->createLocaleMock('en-US');
        $fallbackLocale = $this->createLocaleMock('en');

        $realLocaleParent = $this->createTranslatableMock();
        $realLocaleTranslation = $this->createTranslationMock($realLocaleParent, $locale);
        $realLocaleTranslationFallback = $this->createTranslationMock($realLocaleParent, $fallbackLocale);

        $fallbackLocaleParent = $this->createTranslatableMock();
        $fallbackLocaleTranslation = $this->createTranslationMock($fallbackLocaleParent, $fallbackLocale);

        $translatableLocale = $this->createTranslatableMock($realLocaleTranslation);
        $translatableFallbackLocale = $this->createTranslatableMock($fallbackLocaleTranslation);

        $em = $this->createEmMock();
        $localeSwitcher = $this->createLocaleSwitcherMock();
        $classNameManager = $this->createClassNameManagerMock();
        $comparator = $this->createEntityComparatorMock(
            [
                [$realLocaleParent, $translatableLocale],
                [$fallbackLocaleParent, $translatableFallbackLocale],
            ]
        );

        $repo = new TranslationRepository($em, $localeSwitcher, $classNameManager, $comparator);
        $repo->setItemsTranslated(
            [
                $translatableLocale,
                $translatableFallbackLocale,
            ],
            [
                $realLocaleTranslation,
                $realLocaleTranslationFallback,
                $fallbackLocaleTranslation,
            ],
            $fallbackLocale
        );
    }

    public function testSetItemsTranslatedSingleItem(): void
    {
        $translationParent = $this->createTranslatableMock();
        $translation = $this->createTranslationMock($translationParent);
        $translatable = $this->createTranslatableMock($translation);

        $em = $this->createEmMock();
        $localeSwitcher = $this->createLocaleSwitcherMock();
        $classNameManager = $this->createClassNameManagerMock();
        $comparator = $this->createEntityComparatorMock($translationParent, $translatable);

        $repo = new TranslationRepository($em, $localeSwitcher, $classNameManager, $comparator);
        $repo->setItemsTranslated($translatable, $translation);
    }

    private function createLocaleSwitcherMock(string $locale = self::BASE_LOCALE): LocaleAwareInterface
    {
        /** @var LocaleAwareInterface&MockObject */
        $localeSwitcher = $this->getMockBuilder(LocaleAwareInterface::class)->getMock();

        $localeSwitcher->method('getLocale')->willReturn($locale);

        return $localeSwitcher;
    }

    /**
     * @param array<array<object>>|object $param1
     * @param ?object                     $param2
     */
    private function createEntityComparatorMock(array|object $param1 = [], ?object $param2 = null): EntityComparator
    {
        $equalityMap = \is_array($param1) ? $param1 : [[$param1, $param2]];

        /** @var EntityComparator&MockObject */
        $comparator = $this->getMockBuilder(EntityComparator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $comparator->method('isEqual')->willReturnCallback(
            function (mixed $a, mixed $b) use ($equalityMap): bool {
                foreach ($equalityMap as $mapItem) {
                    if (\in_array($a, $mapItem, true) && \in_array($b, $mapItem, true)) {
                        return true;
                    }
                }

                return false;
            }
        );

        return $comparator;
    }
}
