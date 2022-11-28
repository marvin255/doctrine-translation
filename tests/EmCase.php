<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
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
     * @return EntityManagerProvider&MockObject
     */
    protected function getEntityManagerProviderMock(): EntityManagerProvider
    {
        /** @var EntityManagerProvider&MockObject */
        $provider = $this->getMockBuilder(EntityManagerProvider::class)->disableOriginalConstructor()->getMock();

        return $provider;
    }

    /**
     * @return ClassMetadata&MockObject
     */
    protected function getClassMetadataMock(): ClassMetadata
    {
        /** @var ClassMetadata&MockObject */
        $meta = $this->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $meta;
    }

    /**
     * @return QueryBuilder&MockObject
     */
    protected function getQueryBuilderMock(): QueryBuilder
    {
        /** @var QueryBuilder&MockObject */
        $qb = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $qb;
    }
}
