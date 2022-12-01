<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\EntityManager;

/**
 * Compares two entites by strict comparision and identifiers equality.
 */
class EntityComparator
{
    private readonly EntityManagerProvider $em;

    public function __construct(EntityManagerProvider $em)
    {
        $this->em = $em;
    }

    /**
     * Checks that two doctrin entities are equal.
     */
    public function isEqual(mixed $a, mixed $b): bool
    {
        if (!\is_object($a) || !\is_object($b)) {
            return false;
        } elseif ($a === $b) {
            return true;
        }

        $aClass = \get_class($a);
        $bClass = \get_class($b);

        if ($aClass !== $bClass) {
            return false;
        }

        $meta = $this->em->getClassMetadata($aClass);
        $aId = $meta->getIdentifierValues($a);
        $bId = $meta->getIdentifierValues($b);

        return $aId === $bId;
    }
}
