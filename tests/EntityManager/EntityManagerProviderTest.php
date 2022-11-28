<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\EntityManager;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Marvin255\DoctrineTranslationBundle\EntityManager\EntityComparator;
use Marvin255\DoctrineTranslationBundle\EntityManager\EntityManagerProvider;
use Marvin255\DoctrineTranslationBundle\Tests\EmCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 */
class EntityManagerProviderTest extends EmCase
{
    public function testGetEntityComparator(): void
    {
        $registry = $this->getManagerRegistryMock();

        $provider = new EntityManagerProvider($registry);
        $comparator = $provider->getEntityComparator();
        $comparator1 = $provider->getEntityComparator();

        $this->assertInstanceOf(EntityComparator::class, $comparator);
        $this->assertSame($comparator, $comparator1);
    }

    public function testGetClassMetadataForEntity(): void
    {
        $className = \stdClass::class;
        $entity = new \stdClass();
        $meta = $this->getClassMetadataMock();
        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->equalTo($className))
            ->willReturn($meta);
        $registry = $this->getManagerRegistryMock($className, $em);

        $provider = new EntityManagerProvider($registry);
        $resultMeta = $provider->getClassMetadataForEntity($entity);

        $this->assertSame($meta, $resultMeta);
    }

    public function testGetClassMetadata(): void
    {
        $className = EntityManagerProvider::class;
        $meta = $this->getClassMetadataMock();
        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->equalTo($className))
            ->willReturn($meta);
        $registry = $this->getManagerRegistryMock($className, $em);

        $provider = new EntityManagerProvider($registry);
        $resultMeta = $provider->getClassMetadata($className);

        $this->assertSame($meta, $resultMeta);
    }

    public function testCreateQueryBuilder(): void
    {
        $className = EntityManagerProvider::class;
        $qb = $this->getQueryBuilderMock();
        $em = $this->getEntityManagerMock();
        $em->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($qb);
        $registry = $this->getManagerRegistryMock($className, $em);

        $provider = new EntityManagerProvider($registry);
        $resultQb = $provider->createQueryBuilder($className);

        $this->assertSame($qb, $resultQb);
    }

    public function testGetEntityManagerForClass(): void
    {
        $className = EntityManagerProvider::class;
        $em = $this->getEntityManagerMock();
        $registry = $this->getManagerRegistryMock($className, $em);

        $provider = new EntityManagerProvider($registry);
        $resultEm = $provider->getEntityManagerForClass($className);

        $this->assertSame($em, $resultEm);
    }

    public function testCantGetEntityManagerForClass(): void
    {
        $className = EntityManagerProvider::class;
        $registry = $this->getManagerRegistryMock();

        $provider = new EntityManagerProvider($registry);

        $this->expectException(\InvalidArgumentException::class);
        $provider->getEntityManagerForClass($className);
    }

    /**
     * @param ?string                 $className
     * @param ?EntityManagerInterface $em
     *
     * @return ManagerRegistry&MockObject
     */
    private function getManagerRegistryMock(?string $className = null, ?EntityManagerInterface $em = null): ManagerRegistry
    {
        /** @var ManagerRegistry&MockObject */
        $mr = $this->getMockBuilder(ManagerRegistry::class)->disableOriginalConstructor()->getMock();

        if ($className !== null) {
            $mr->method('getManagerForClass')->with($this->equalTo($className))->willReturn($em);
        } else {
            $mr->method('getManagerForClass')->willReturn(null);
        }

        return $mr;
    }

    /**
     * @return EntityManagerInterface&MockObject
     */
    private function getEntityManagerMock(): EntityManagerInterface
    {
        /** @var EntityManagerInterface&MockObject */
        $em = $this->getMockBuilder(EntityManagerInterface::class)->disableOriginalConstructor()->getMock();

        return $em;
    }
}
