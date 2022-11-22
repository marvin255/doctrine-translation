<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Marvin255\DoctrineTranslationBundle\Entity\Translation;
use Marvin255\DoctrineTranslationBundle\Repository\EntityComparator;
use Marvin255\DoctrineTranslationBundle\Repository\TranslationRepository;
use Marvin255\DoctrineTranslationBundle\Tests\BaseCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\Translation\LocaleAwareInterface;

/**
 * @internal
 */
class TranslationRepositoryTest extends BaseCase
{
    public const TRANSLATABLES_WHERE = TranslationRepository::QUERY_ALIAS . '.' . Translation::TRANSLATABLE_FIELD_NAME . ' IN (:translatables)';
    public const LOCALES_WHERE = [[TranslationRepository::QUERY_ALIAS . '.' . Translation::LOCALE_FIELD_NAME . ' IN (:locales)']];

    public function testFindAndSetTranslationForCurrentLocale(): void
    {
        $translationParent = $this->createTranslatableMock();
        $translation = $this->createTranslationMock($translationParent);
        $translatable = $this->createTranslatableMock([$translation]);

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

        $em = $this->createEmMock($qb);
        $localeSwitcher = $this->createLocaleSwitcherMock();
        $classNameManager = $this->createBasicClassNameManagerMock($translatable, $translation);
        $comparator = $this->createEntityComparatorMock($translationParent, $translatable);

        $repo = new TranslationRepository($em, $localeSwitcher, $classNameManager, $comparator);
        $repo->findAndSetTranslationForCurrentLocale($translatable);
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

        $em = $this->createEmMock($qb);
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
        $translatable = $this->createTranslatableMock([$translation]);
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

        $em = $this->createEmMock($qb);
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

        $em = $this->createEmMock([$qb, $qb1]);
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

        $em = $this->createEmMock($qb);
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

        $em = $this->createEmMock($qb);
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

        $translatableEqual = $this->createTranslatableMock([$translationEqual, $translationEqualSecond]);

        $translationNonEqual = $this->createTranslationMock();
        $translatableNonEqual = $this->createTranslatableMock([]);

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

        $translatableLocale = $this->createTranslatableMock([$realLocaleTranslation]);
        $translatableFallbackLocale = $this->createTranslatableMock([$fallbackLocaleTranslation]);

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
        $translatable = $this->createTranslatableMock([$translation]);

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
     * @psalm-param QueryBuilder[]|QueryBuilder $qb
     */
    private function createEmMock(array|QueryBuilder $qb = []): EntityManagerInterface
    {
        /** @var EntityManagerInterface&MockObject */
        $em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();

        $qb = $qb instanceof QueryBuilder ? [$qb] : $qb;
        if (!empty($qb)) {
            $em->expects($this->exactly(\count($qb)))
                ->method('createQueryBuilder')
                ->willReturnOnConsecutiveCalls(...$qb);
        } else {
            $em->expects($this->never())->method('createQueryBuilder');
        }

        return $em;
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

    /**
     * @psalm-param array<string, mixed>|null $queryParts
     * @psalm-param array<int, mixed> $results
     *
     * @psalm-suppress MixedArgument
     */
    private function createQueryBuilderMock(?array $queryParts = null, array $results = []): QueryBuilder
    {
        /** @var QueryBuilder&MockObject */
        $qb = $this->getMockBuilder(QueryBuilder::class)->disableOriginalConstructor()->getMock();

        if (isset($queryParts['select']) && \is_string($queryParts['select'])) {
            $qb->expects($this->once())
                ->method('select')
                ->with($this->equalTo($queryParts['select']))
                ->willReturnSelf();
        } else {
            $qb->method('select')->willReturnSelf();
        }

        if (isset($queryParts['from']) && \is_array($queryParts['from'])) {
            $qb->expects($this->once())
                ->method('from')
                ->with(
                    $this->equalTo($queryParts['from'][0] ?? null),
                    $this->equalTo($queryParts['from'][1] ?? null)
                )
                ->willReturnSelf();
        } else {
            $qb->method('from')->willReturnSelf();
        }

        if (isset($queryParts['where']) && \is_string($queryParts['where'])) {
            $qb->expects($this->once())
                ->method('where')
                ->with($this->equalTo($queryParts['where']))
                ->willReturnSelf();
        } else {
            $qb->method('where')->willReturnSelf();
        }

        if (isset($queryParts['andWhere']) && \is_array($queryParts['andWhere'])) {
            $qb->expects($this->exactly(\count($queryParts['andWhere'])))
                ->method('andWhere')
                ->withConsecutive(...$queryParts['andWhere'])
                ->willReturnSelf();
        } else {
            $qb->method('andWhere')->willReturnSelf();
        }

        if (isset($queryParts['setParameter']) && \is_array($queryParts['setParameter'])) {
            $qb->expects($this->exactly(\count($queryParts['setParameter'])))
                ->method('setParameter')
                ->withConsecutive(...$queryParts['setParameter'])
                ->willReturnSelf();
        } else {
            $qb->method('setParameter')->willReturnSelf();
        }

        /** @var AbstractQuery&MockObject */
        $query = $this->getMockBuilder(AbstractQuery::class)->disableOriginalConstructor()->getMock();
        $query->method('getResult')->willReturn($results);

        $qb->method('getQuery')->willReturn($query);

        return $qb;
    }
}
