<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Marvin255\DoctrineTranslationBundle\EntityManager\EntityComparator;
use Marvin255\DoctrineTranslationBundle\EntityManager\EntityManagerProvider;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Abstract class with entity manager related helpers.
 */
abstract class EmCase extends BaseCase
{
    /**
     * @param array<string, ClassMetadata>|string $map
     * @param ?ClassMetadata                      $meta
     *
     * @return EntityManagerProvider&MockObject
     */
    protected function getEntityManagerProviderMockMeta(array|string $map = [], ?ClassMetadata $meta = null): EntityManagerProvider
    {
        $provider = $this->getEntityManagerProviderMock();

        $map = \is_array($map) ? $map : [$map => $meta];
        $provider->method('getClassMetadata')->willReturnCallback(
            fn (string $toCheck): ?ClassMetadata => $map[$toCheck] ?? null
        );

        return $provider;
    }

    /**
     * @param array<string, QueryBuilder>|QueryBuilder $map
     * @param EntityComparator|null                    $comparator
     *
     * @return EntityManagerProvider&MockObject
     */
    protected function getEntityManagerProviderMockQueryBuilder(array|QueryBuilder $map = [], ?EntityComparator $comparator = null): EntityManagerProvider
    {
        $provider = $this->getEntityManagerProviderMock();

        $map = \is_array($map) ? $map : [self::BASE_TRANSLATION_CLASS => $map];
        $provider->method('createQueryBuilder')->willReturnCallback(
            fn (string $toCheck): ?QueryBuilder => $map[$toCheck] ?? null
        );

        if ($comparator) {
            $provider->method('getEntityComparator')->willReturn($comparator);
        }

        return $provider;
    }

    /**
     * @return EntityManagerProvider&MockObject
     */
    protected function getEntityManagerProviderMockComparator(EntityComparator $comparator): EntityManagerProvider
    {
        $provider = $this->getEntityManagerProviderMock();
        $provider->method('getEntityComparator')->willReturn($comparator);

        return $provider;
    }

    /**
     * @return EntityManagerProvider&MockObject
     */
    protected function getEntityManagerProviderMock(): EntityManagerProvider
    {
        /** @var EntityManagerProvider&MockObject */
        $provider = $this->getMockBuilder(EntityManagerProvider::class)->disableOriginalConstructor()->getMock();

        return $provider;
    }

    /**
     * @return EntityManagerInterface&MockObject
     */
    protected function getEntityManagerMock(): EntityManagerInterface
    {
        /** @var EntityManagerInterface&MockObject */
        $em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();

        return $em;
    }

    /**
     * @return ClassMetadata&MockObject
     */
    protected function createClassMetadataMock(): ClassMetadata
    {
        /** @var ClassMetadata&MockObject */
        $meta = $this->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $meta;
    }

    /**
     * @psalm-param array<string, mixed> $queryParts
     * @psalm-param array<int, mixed> $results
     *
     * @psalm-suppress MixedArgument
     *
     * @return QueryBuilder&MockObject
     */
    protected function createQueryBuilderMock(array $queryParts = [], array $results = []): QueryBuilder
    {
        /** @var QueryBuilder&MockObject */
        $qb = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        if (isset($queryParts['select']) && \is_string($queryParts['select'])) {
            $qb->expects($this->once())
                ->method('select')
                ->with($this->equalTo($queryParts['select']))
                ->willReturnSelf();
        }

        if (isset($queryParts['from']) && \is_array($queryParts['from'])) {
            $qb->expects($this->once())
                ->method('from')
                ->with(
                    $this->equalTo($queryParts['from'][0] ?? null),
                    $this->equalTo($queryParts['from'][1] ?? null)
                )
                ->willReturnSelf();
        }

        if (isset($queryParts['where']) && \is_string($queryParts['where'])) {
            $qb->expects($this->once())
                ->method('where')
                ->with($this->equalTo($queryParts['where']))
                ->willReturnSelf();
        }

        if (isset($queryParts['andWhere']) && \is_array($queryParts['andWhere'])) {
            $qb->expects($this->exactly(\count($queryParts['andWhere'])))
                ->method('andWhere')
                ->withConsecutive(...$queryParts['andWhere'])
                ->willReturnSelf();
        }

        if (isset($queryParts['setParameter']) && \is_array($queryParts['setParameter'])) {
            $qb->expects($this->exactly(\count($queryParts['setParameter'])))
                ->method('setParameter')
                ->withConsecutive(...$queryParts['setParameter'])
                ->willReturnSelf();
        }

        /** @var AbstractQuery&MockObject */
        $query = $this->getMockBuilder(AbstractQuery::class)->disableOriginalConstructor()->getMock();
        $query->method('getResult')->willReturn($results);

        $qb->method('getQuery')->willReturn($query);

        return $qb;
    }

    /**
     * @param array<array<object>>|object $param1
     * @param ?object                     $param2
     */
    protected function createEntityComparatorMock(array|object $param1 = [], ?object $param2 = null): EntityComparator
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
