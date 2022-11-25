<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Abstract class with entity manager related helpers.
 */
abstract class EmCase extends BaseCase
{
    /**
     * @psalm-param array<int, mixed[]> $data
     */
    protected function createMetaMock(array $data = []): ClassMetadata
    {
        /** @var ClassMetadata&MockObject */
        $meta = $this->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $meta->method('getIdentifierValues')->willReturnCallback(
            function (object $toCheck) use ($data): array {
                $return = [mt_rand(), mt_rand()];
                foreach ($data as $datum) {
                    if (isset($datum[0]) && $datum[0] === $toCheck) {
                        $return = (array) ($datum[1] ?? []);
                    }
                }

                return $return;
            }
        );

        return $meta;
    }

    /**
     * @psalm-param array<string, mixed>|null $queryParts
     * @psalm-param array<int, mixed> $results
     *
     * @psalm-suppress MixedArgument
     */
    protected function createQueryBuilderMock(?array $queryParts = null, array $results = []): QueryBuilder
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

    /**
     * @psalm-param array<string, ClassMetadata> $classMetaDataMap
     * @psalm-param QueryBuilder[]|QueryBuilder $qb
     */
    protected function createEmMock(array $classMetaDataMap = [], array|QueryBuilder $qb = []): EntityManagerInterface
    {
        /** @var EntityManagerInterface&MockObject */
        $em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();

        $em->method('getClassMetadata')->willReturnCallback(
            fn (string $toCheck): ?ClassMetadata => $classMetaDataMap[$toCheck] ?? null
        );

        $qb = $qb instanceof QueryBuilder ? [$qb] : $qb;
        if (!empty($qb)) {
            $em->expects($this->exactly(\count($qb)))
                ->method('createQueryBuilder')
                ->willReturnOnConsecutiveCalls(...$qb);
        }

        return $em;
    }
}
