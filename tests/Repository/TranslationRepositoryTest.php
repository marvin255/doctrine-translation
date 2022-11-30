<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\Repository;

use Marvin255\DoctrineTranslationBundle\Entity\Translatable;
use Marvin255\DoctrineTranslationBundle\Entity\Translation;
use Marvin255\DoctrineTranslationBundle\Repository\TranslationRepository;
use Marvin255\DoctrineTranslationBundle\Tests\EmCase;

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
        $translatable = $this->createTranslatableMockWithSetTranslated($translation);

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

        $comparator = $this->createEntityComparatorMock($translationParent, $translatable);
        $em = $this->getEntityManagerProviderMockQueryBuilder($qb, $comparator);
        $localeProvider = $this->createLocaleProviderMock();
        $classNameManager = $this->createBasicClassNameManagerMock($translatable, $translation);

        $repo = new TranslationRepository($em, $localeProvider, $classNameManager);
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
        $translatable = $this->createTranslatableMockWithSetTranslated($translation);

        $translationParentFallback = $this->createTranslatableMock();
        $translationFallback = $this->createTranslationMock($translationParentFallback, $defaultLocale);
        $translatableFallback = $this->createTranslatableMockWithSetTranslated($translationFallback);

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

        $comparator = $this->createEntityComparatorMock([
            [$translationParent, $translatable],
            [$translationParentFallback, $translatableFallback],
        ]);
        $em = $this->getEntityManagerProviderMockQueryBuilder($qb, $comparator);
        $localeProvider = $this->createLocaleProviderMock($localeStr, $defaultLocaleStr);
        $classNameManager = $this->createClassNameManagerMock(
            self::BASE_CLASS_NAMES_MAP,
            [
                self::BASE_TRANSLATABLE_CLASS => [$translatable, $translatableFallback],
            ]
        );

        $repo = new TranslationRepository($em, $localeProvider, $classNameManager);
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

        $em = $this->getEntityManagerProviderMockQueryBuilder($qb);
        $localeProvider = $this->createLocaleProviderMock();
        $classNameManager = $this->createClassNameManagerMock(
            self::BASE_CLASS_NAMES_MAP,
            [
                self::BASE_TRANSLATABLE_CLASS => $translatables,
            ]
        );

        $repo = new TranslationRepository($em, $localeProvider, $classNameManager);
        $res = $repo->findTranslationForCurrentLocale($translatables);

        $this->assertSame($reference, $res);
    }

    public function testFindAndSetTranslationForLocale(): void
    {
        $translationParent = $this->createTranslatableMock();
        $translation = $this->createTranslationMock($translationParent);
        $translatable = $this->createTranslatableMockWithSetTranslated($translation);
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

        $comparator = $this->createEntityComparatorMock($translationParent, $translatable);
        $em = $this->getEntityManagerProviderMockQueryBuilder($qb, $comparator);
        $localeProvider = $this->createLocaleProviderMock();
        $classNameManager = $this->createBasicClassNameManagerMock($translatable, $translation);

        $repo = new TranslationRepository($em, $localeProvider, $classNameManager);
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

        $em = $this->getEntityManagerProviderMockQueryBuilder(
            [
                $translationClass => $qb,
                $translationClass1 => $qb1,
            ]
        );
        $localeProvider = $this->createLocaleProviderMock();
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

        $repo = new TranslationRepository($em, $localeProvider, $classNameManager);
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

        $em = $this->getEntityManagerProviderMockQueryBuilder($qb);
        $localeProvider = $this->createLocaleProviderMock();
        $classNameManager = $this->createBasicClassNameManagerMock($translatable);

        $repo = new TranslationRepository($em, $localeProvider, $classNameManager);
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

        $em = $this->getEntityManagerProviderMockQueryBuilder($qb);
        $localeProvider = $this->createLocaleProviderMock();
        $classNameManager = $this->createBasicClassNameManagerMock($translatable);

        $repo = new TranslationRepository($em, $localeProvider, $classNameManager);
        $res = $repo->findTranslations($translatable);

        $this->assertSame($reference, $res);
    }

    public function testFindTranslationsEmptyTranslatableList(): void
    {
        $em = $this->getEntityManagerProviderMockQueryBuilder();
        $localeProvider = $this->createLocaleProviderMock();
        $classNameManager = $this->createClassNameManagerMock();

        $repo = new TranslationRepository($em, $localeProvider, $classNameManager);
        $res = $repo->findTranslations([]);

        $this->assertSame([], $res);
    }

    public function testSetItemsTranslated(): void
    {
        $translationEqualParent = $this->createTranslatableMock();
        $translationEqual = $this->createTranslationMock($translationEqualParent);

        $translationEqualParentSecond = $this->createTranslatableMock();
        $translationEqualSecond = $this->createTranslationMock($translationEqualParentSecond);

        $translatableEqual = $this->createTranslatableMockWithSetTranslated($translationEqual);

        $translationNonEqual = $this->createTranslationMock();
        $translatableNonEqual = $this->createTranslatableMockWithSetTranslated(null);

        $comparator = $this->createEntityComparatorMock(
            [
                [$translationEqualParent, $translatableEqual],
                [$translationEqualParentSecond, $translatableEqual],
            ]
        );
        $em = $this->getEntityManagerProviderMockComparator($comparator);
        $localeProvider = $this->createLocaleProviderMock();
        $classNameManager = $this->createClassNameManagerMock();

        $repo = new TranslationRepository($em, $localeProvider, $classNameManager);
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

        $translatableLocale = $this->createTranslatableMockWithSetTranslated($realLocaleTranslation);
        $translatableFallbackLocale = $this->createTranslatableMockWithSetTranslated($fallbackLocaleTranslation);

        $comparator = $this->createEntityComparatorMock(
            [
                [$realLocaleParent, $translatableLocale],
                [$fallbackLocaleParent, $translatableFallbackLocale],
            ]
        );
        $em = $this->getEntityManagerProviderMockComparator($comparator);
        $localeProvider = $this->createLocaleProviderMock();
        $classNameManager = $this->createClassNameManagerMock();

        $repo = new TranslationRepository($em, $localeProvider, $classNameManager);
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
        $translatable = $this->createTranslatableMockWithSetTranslated($translation);

        $comparator = $this->createEntityComparatorMock($translationParent, $translatable);
        $em = $this->getEntityManagerProviderMockComparator($comparator);
        $localeProvider = $this->createLocaleProviderMock();
        $classNameManager = $this->createClassNameManagerMock();

        $repo = new TranslationRepository($em, $localeProvider, $classNameManager);
        $repo->setItemsTranslated($translatable, $translation);
    }

    private function createTranslatableMockWithSetTranslated(Translation|false|null $translated = false): Translatable
    {
        $translatable = $this->createTranslatableMock();

        if ($translated === false) {
            $translatable->expects($this->never())->method('setTranslated');
        } else {
            $translatable->expects($this->once())
                ->method('setTranslated')
                ->with($this->identicalTo($translated))
                ->willReturnSelf();
        }

        return $translatable;
    }
}
