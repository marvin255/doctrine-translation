<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
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

        $em = $this->createEmMock($qb, $qb1);
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
     * @psalm-param class-string $class
     */
    private function createTranslatableMock(string $class = Translatable::class): Translatable
    {
        /** @var Translatable&MockObject */
        $translatable = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();

        return $translatable;
    }

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
     * @psalm-param QueryBuilder[] $qb
     */
    private function createEmMock(...$qb): EntityManagerInterface
    {
        /** @var EntityManagerInterface&MockObject */
        $em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();

        $em->expects($this->exactly(\count($qb)))
            ->method('createQueryBuilder')
            ->willReturnOnConsecutiveCalls(...$qb);

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
        }

        if (isset($queryParts['from']) && \is_array($queryParts['from'])) {
            $qb->expects($this->once())
                ->method('from')
                ->with(
                    $this->equalTo($queryParts['from'][0] ?? null),
                    $this->equalTo($queryParts['from'][1] ?? null)
                )
                ->willReturn($qb);
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
            $qb->expects($this->never())->method('where');
        }

        /** @var AbstractQuery&MockObject */
        $query = $this->getMockBuilder(AbstractQuery::class)->disableOriginalConstructor()->getMock();
        $query->method('getResult')->willReturn($results);

        $qb->method('getQuery')->willReturn($query);

        return $qb;
    }
}
