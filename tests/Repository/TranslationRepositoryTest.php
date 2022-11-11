<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Marvin255\DoctrineTranslationBundle\ClassNameManager\ClassNameManager;
use Marvin255\DoctrineTranslationBundle\Entity\Translatable;
use Marvin255\DoctrineTranslationBundle\Entity\Translation;
use Marvin255\DoctrineTranslationBundle\Locale\Locale;
use Marvin255\DoctrineTranslationBundle\Repository\TranslationRepository;
use Marvin255\DoctrineTranslationBundle\Tests\BaseCase;
use Marvin255\DoctrineTranslationBundle\Tests\Mock\MockTranslatableItem;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Translation\LocaleSwitcher;

/**
 * @internal
 */
class TranslationRepositoryTest extends BaseCase
{
    public function testFindTranslationForCurrentLocale(): void
    {
        $reference = [
            $this->createTranslationMock(),
            $this->createTranslationMock(),
        ];
        $translatable = $this->createTranslatableMock();
        $translatable1 = $this->createTranslatableMock();
        $translationClass = 'translation';
        $classNameMap = [
            \get_class($translatable) => $translationClass,
            \get_class($translatable1) => $translationClass,
        ];
        $translatables = [$translatable, $translatable1];
        $localeString = 'en-US';

        $qb = $this->createQueryBuilderMock(
            [
                'select' => TranslationRepository::QUERY_ALIAS,
                'from' => [$translationClass, TranslationRepository::QUERY_ALIAS],
                'where' => TranslationRepository::QUERY_ALIAS . '.' . Translation::TRANSLATABLE_FIELD_NAME . ' IN (:translatables)',
                'andWhere' => [
                    [TranslationRepository::QUERY_ALIAS . '.' . Translation::LOCALE_FIELD_NAME . ' IN (:locales)'],
                ],
                'setParameter' => [
                    ['translatables', $translatables],
                    ['locales', [$localeString]],
                ],
            ],
            $reference
        );

        $em = $this->createEmMock($qb);
        $localeSwitcher = $this->createLocaleSwitcherMock($localeString);
        $classNameManager = $this->createClassNameManagerMock($classNameMap);

        $repo = new TranslationRepository($em, $localeSwitcher, $classNameManager);
        $res = $repo->findTranslationForCurrentLocale($translatables);

        $this->assertSame($reference, $res);
    }

    public function testFindTranslations(): void
    {
        $reference = [
            $this->createTranslationMock(),
            $this->createTranslationMock(),
        ];
        $translatable = $this->createTranslatableMock();
        $translatable1 = $this->createTranslatableMock();
        $translatable2 = $this->createTranslatableMock(MockTranslatableItem::class);
        $translationClass = 'translation';
        $translationClass1 = 'translation1';
        $classNameMap = [
            \get_class($translatable) => $translationClass,
            \get_class($translatable1) => $translationClass,
            \get_class($translatable2) => $translationClass1,
        ];
        $localeString = 'en-US';
        $locale = $this->createLocaleMock($localeString);
        $localeString1 = 'en-GB';
        $locale1 = $this->createLocaleMock($localeString1);

        $qb = $this->createQueryBuilderMock(
            [
                'select' => TranslationRepository::QUERY_ALIAS,
                'from' => [$translationClass, TranslationRepository::QUERY_ALIAS],
                'where' => TranslationRepository::QUERY_ALIAS . '.' . Translation::TRANSLATABLE_FIELD_NAME . ' IN (:translatables)',
                'andWhere' => [
                    [TranslationRepository::QUERY_ALIAS . '.' . Translation::LOCALE_FIELD_NAME . ' IN (:locales)'],
                ],
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
                'where' => TranslationRepository::QUERY_ALIAS . '.' . Translation::TRANSLATABLE_FIELD_NAME . ' IN (:translatables)',
                'andWhere' => [
                    [TranslationRepository::QUERY_ALIAS . '.' . Translation::LOCALE_FIELD_NAME . ' IN (:locales)'],
                ],
                'setParameter' => [
                    ['translatables', [$translatable2]],
                    ['locales', [$localeString, $localeString1]],
                ],
            ],
            [$reference[1]]
        );

        $em = $this->createEmMock([$qb, $qb1]);
        $localeSwitcher = $this->createLocaleSwitcherMock();
        $classNameManager = $this->createClassNameManagerMock($classNameMap);

        $repo = new TranslationRepository($em, $localeSwitcher, $classNameManager);
        $res = $repo->findTranslations([$translatable, $translatable1, $translatable2], [$locale, $locale1]);

        $this->assertSame($reference, $res);
    }

    public function testFindTranslationsSingleItem(): void
    {
        $reference = [
            $this->createTranslationMock(),
            $this->createTranslationMock(),
        ];
        $translatable = $this->createTranslatableMock();
        $translationClass = 'translation';
        $classNameMap = [
            \get_class($translatable) => $translationClass,
        ];

        $qb = $this->createQueryBuilderMock(
            [
                'select' => TranslationRepository::QUERY_ALIAS,
                'from' => [$translationClass, TranslationRepository::QUERY_ALIAS],
                'where' => TranslationRepository::QUERY_ALIAS . '.' . Translation::TRANSLATABLE_FIELD_NAME . ' IN (:translatables)',
                'setParameter' => [
                    ['translatables', [$translatable]],
                ],
            ],
            $reference
        );

        $em = $this->createEmMock($qb);
        $localeSwitcher = $this->createLocaleSwitcherMock();
        $classNameManager = $this->createClassNameManagerMock($classNameMap);

        $repo = new TranslationRepository($em, $localeSwitcher, $classNameManager);
        $res = $repo->findTranslations($translatable);

        $this->assertSame($reference, $res);
    }

    public function testFindTranslationsNoLocale(): void
    {
        $reference = [$this->createTranslationMock()];
        $translatable = $this->createTranslatableMock();
        $translationClass = 'translation';
        $classNameMap = [
            \get_class($translatable) => $translationClass,
        ];

        $qb = $this->createQueryBuilderMock(
            [
                'select' => TranslationRepository::QUERY_ALIAS,
                'from' => [$translationClass, TranslationRepository::QUERY_ALIAS],
                'where' => TranslationRepository::QUERY_ALIAS . '.' . Translation::TRANSLATABLE_FIELD_NAME . ' IN (:translatables)',
                'setParameter' => [
                    ['translatables', [$translatable]],
                ],
            ],
            $reference
        );

        $em = $this->createEmMock($qb);
        $localeSwitcher = $this->createLocaleSwitcherMock();
        $classNameManager = $this->createClassNameManagerMock($classNameMap);

        $repo = new TranslationRepository($em, $localeSwitcher, $classNameManager);
        $res = $repo->findTranslations([$translatable]);

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

    /**
     * @psalm-suppress MixedMethodCall
     */
    public function testSetCurrentTranslation(): void
    {
        $translationParent = $this->createTranslatableMock(Translatable::class, '0');
        $translation = $this->createTranslationMock();
        $translation->method('getTranslatable')->willReturn($translationParent);

        $translationParent1 = $this->createTranslatableMock(Translatable::class, '1');
        $translation1 = $this->createTranslationMock();
        $translation1->method('getTranslatable')->willReturn($translationParent1);

        $translation2 = $this->createTranslationMock();
        $translation2->method('getTranslatable')->willReturn(null);

        $translationParent3 = $this->createTranslatableMock(Translatable::class, '1');
        $translation3 = $this->createTranslationMock();
        $translation3->method('getTranslatable')->willReturn($translationParent3);

        $translationParent4 = $this->createTranslatableMock(MockTranslatableItem::class, '2');
        $translation4 = $this->createTranslationMock();
        $translation4->method('getTranslatable')->willReturn($translationParent4);

        $translatable = $this->createTranslatableMock(Translatable::class, '1');
        $translatable->expects($this->once())
            ->method('setCurrentTranslation')
            ->with($this->identicalTo($translation1))
            ->willReturnSelf();

        $translatable1 = $this->createTranslatableMock(Translatable::class, '2');
        $translatable1->expects($this->once())
            ->method('setCurrentTranslation')
            ->with($this->equalTo(null))
            ->willReturnSelf();

        /** @var ClassMetadata&MockObject */
        $meta = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
        $meta->method('getIdentifierValues')->willReturnCallback(fn (object $toCheck): array => [$toCheck->getId()]);

        $em = $this->createEmMock([], [\get_class($translatable) => $meta]);
        $localeSwitcher = $this->createLocaleSwitcherMock();
        $classNameManager = $this->createClassNameManagerMock();

        $repo = new TranslationRepository($em, $localeSwitcher, $classNameManager);
        $repo->setCurrentTranslation(
            [$translatable, $translatable1],
            [$translation, $translation1, $translation2, $translation3, $translation4]
        );
    }

    /**
     * @psalm-suppress MixedMethodCall
     */
    public function testSetCurrentTranslationSingleItem(): void
    {
        $translationParent = $this->createTranslatableMock(Translatable::class, '1');
        $translation = $this->createTranslationMock();
        $translation->method('getTranslatable')->willReturn($translationParent);

        $translatable = $this->createTranslatableMock(Translatable::class, '1');
        $translatable->expects($this->once())
            ->method('setCurrentTranslation')
            ->with($this->identicalTo($translation))
            ->willReturnSelf();

        /** @var ClassMetadata&MockObject */
        $meta = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
        $meta->method('getIdentifierValues')->willReturnCallback(fn (object $toCheck): array => [$toCheck->getId()]);

        $em = $this->createEmMock([], [\get_class($translatable) => $meta]);
        $localeSwitcher = $this->createLocaleSwitcherMock();
        $classNameManager = $this->createClassNameManagerMock();

        $repo = new TranslationRepository($em, $localeSwitcher, $classNameManager);
        $repo->setCurrentTranslation($translatable, $translation);
    }

    /**
     * @psalm-param class-string $class
     *
     * @return Translatable&MockObject
     */
    private function createTranslatableMock(string $class = Translatable::class, ?string $id = null): Translatable
    {
        /** @var Translatable&MockObject */
        $translatable = $this->getMockBuilder($class)
            ->addMethods(['getId'])
            ->onlyMethods(['setCurrentTranslation'])
            ->disableOriginalConstructor()
            ->getMock();

        if ($id !== null) {
            $translatable->method('getId')->willReturn($id);
        } else {
            $translatable->expects($this->never())->method('getId');
        }

        return $translatable;
    }

    /**
     * @return Translation&MockObject
     */
    private function createTranslationMock(): Translation
    {
        /** @var Translation&MockObject */
        $translation = $this->getMockBuilder(Translation::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $translation;
    }

    private function createLocaleMock(string $localeString = ''): Locale
    {
        /** @var Locale&MockObject */
        $locale = $this->getMockBuilder(Locale::class)
            ->disableOriginalConstructor()
            ->getMock();

        $locale->method('getFull')->willReturn($localeString);

        return $locale;
    }

    /**
     * @psalm-param QueryBuilder[]|QueryBuilder $qb
     * @psalm-param array<string, ClassMetadata> $meta
     */
    private function createEmMock(array|QueryBuilder $qb = [], array $meta = []): EntityManagerInterface
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

        if (!empty($meta)) {
            $em->method('getClassMetadata')->willReturnCallback(
                function (string $toCheck) use ($meta): ClassMetadata {
                    if (!isset($meta[$toCheck])) {
                        throw new \RuntimeException("Metadata for {$toCheck} not found");
                    }

                    return $meta[$toCheck];
                }
            );
        } else {
            $em->expects($this->never())->method('getClassMetadata');
        }

        return $em;
    }

    private function createLocaleSwitcherMock(string $locale = ''): LocaleSwitcher
    {
        /** @var LocaleSwitcher&MockObject */
        $localeSwitcher = $this->getMockBuilder(LocaleSwitcher::class)
            ->disableOriginalConstructor()
            ->getMock();

        $localeSwitcher->method('getLocale')->willReturn($locale);

        return $localeSwitcher;
    }

    /**
     * @psalm-param array<string, string> $map
     */
    private function createClassNameManagerMock(array $map = []): ClassNameManager
    {
        /** @var ClassNameManager&MockObject */
        $classNameManager = $this->getMockBuilder(ClassNameManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $classNameManager->method('getTranslationClassForTranslatable')->willReturnCallback(
            fn (string $toCheck): string => $map[$toCheck] ?? ''
        );

        $classNameManager->method('getTranslationClassForTranslatableEntity')->willReturnCallback(
            fn (object $toCheck): string => $map[\get_class($toCheck)] ?? ''
        );

        return $classNameManager;
    }

    /**
     * @psalm-param array<string, mixed> $queryParts
     * @psalm-param array<int, mixed> $results
     *
     * @psalm-suppress MixedArgument
     */
    private function createQueryBuilderMock(array $queryParts = [], array $results = []): QueryBuilder
    {
        /** @var QueryBuilder&MockObject */
        $qb = $this->getMockBuilder(QueryBuilder::class)->disableOriginalConstructor()->getMock();

        if (isset($queryParts['select']) && \is_string($queryParts['select'])) {
            $qb->expects($this->once())
                ->method('select')
                ->with($this->equalTo($queryParts['select']))
                ->willReturn($qb);
        } else {
            $qb->expects($this->never())->method('select');
        }

        if (isset($queryParts['from']) && \is_array($queryParts['from'])) {
            $qb->expects($this->once())
                ->method('from')
                ->with(
                    $this->equalTo($queryParts['from'][0] ?? null),
                    $this->equalTo($queryParts['from'][1] ?? null)
                )
                ->willReturn($qb);
        } else {
            $qb->expects($this->never())->method('from');
        }

        if (isset($queryParts['where']) && \is_string($queryParts['where'])) {
            $qb->expects($this->once())
                ->method('where')
                ->with($this->equalTo($queryParts['where']))
                ->willReturn($qb);
        } else {
            $qb->expects($this->never())->method('where');
        }

        if (isset($queryParts['andWhere']) && \is_array($queryParts['andWhere'])) {
            $qb->expects($this->exactly(\count($queryParts['andWhere'])))
                ->method('andWhere')
                ->withConsecutive(...$queryParts['andWhere'])
                ->willReturn($qb);
        } else {
            $qb->expects($this->never())->method('andWhere');
        }

        if (isset($queryParts['setParameter']) && \is_array($queryParts['setParameter'])) {
            $qb->expects($this->exactly(\count($queryParts['setParameter'])))
                ->method('setParameter')
                ->withConsecutive(...$queryParts['setParameter'])
                ->willReturn($qb);
        } else {
            $qb->expects($this->never())->method('setParameter');
        }

        /** @var AbstractQuery&MockObject */
        $query = $this->getMockBuilder(AbstractQuery::class)->disableOriginalConstructor()->getMock();
        $query->method('getResult')->willReturn($results);

        $qb->method('getQuery')->willReturn($query);

        return $qb;
    }
}
