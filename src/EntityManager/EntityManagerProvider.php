<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\EntityManager;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository that can query translations for items.
 */
class EntityManagerProvider
{
    private readonly ManagerRegistry $managerRegistry;

    private ?EntityComparator $comparator = null;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * Return entity comparator for the given class.
     */
    public function getEntityComparator(): EntityComparator
    {
        if ($this->comparator === null) {
            $this->comparator = new EntityComparator($this);
        }

        return $this->comparator;
    }

    /**
     * Return class metadata item for the given object.
     */
    public function getClassMetadataForEntity(object $enity): ClassMetadata
    {
        $class = \get_class($enity);

        return $this->getClassMetadata($class);
    }

    /**
     * Return class metadata item for the given class.
     *
     * @psalm-param class-string $class
     */
    public function getClassMetadata(string $class): ClassMetadata
    {
        return $this->getEntityManagerForClass($class)->getClassMetadata($class);
    }

    /**
     * Return query builder related to the given entity class.
     *
     * @psalm-param class-string $class
     */
    public function createQueryBuilder(string $class): QueryBuilder
    {
        return $this->getEntityManagerForClass($class)->createQueryBuilder();
    }

    /**
     * Return entity manager for the given entity class.
     *
     * @psalm-param class-string $class
     */
    public function getEntityManagerForClass(string $class): EntityManagerInterface
    {
        $em = $this->managerRegistry->getManagerForClass($class);

        if (!($em instanceof EntityManagerInterface)) {
            throw new \InvalidArgumentException("Can't find entity manager for class '{$class}'");
        }

        return $em;
    }
}
