<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\EntityManager;

use Doctrine\ORM\Mapping\ClassMetadata;
use Marvin255\DoctrineTranslationBundle\EntityManager\EntityComparator;
use Marvin255\DoctrineTranslationBundle\EntityManager\EntityManagerProvider;
use Marvin255\DoctrineTranslationBundle\Tests\EmCase;

/**
 * @internal
 */
class EntityComparatorTest extends EmCase
{
    /**
     * @dataProvider provideIsEqual
     */
    public function testIsEqual(mixed $a, mixed $b, bool $reference, ?EntityManagerProvider $em = null): void
    {
        $em = $em ?: $this->getEntityManagerProviderMockMeta();

        $comparator = new EntityComparator($em);
        $res = $comparator->isEqual($a, $b);

        $this->assertSame($reference, $res);
    }

    public function provideIsEqual(): array
    {
        $a = new \stdClass();
        $b = new \stdClass();

        return [
            'first param is not an object' => [
                'test',
                $this,
                false,
            ],
            'second param is not an object' => [
                $this,
                'test',
                false,
            ],
            'both params are not objects' => [
                'test',
                'test',
                false,
            ],
            'same object in both params' => [
                $this,
                $this,
                true,
            ],
            'objects with different classes' => [
                $this,
                $a,
                false,
            ],
            'equal by id' => [
                $a,
                $b,
                true,
                $this->getEntityManagerProviderMockMeta(
                    \stdClass::class,
                    $this->getClassMetadataMockIdentifiers([$a, 1, 2], [$b, 1, 2])
                ),
            ],
            "don't equal by id" => [
                $a,
                $b,
                false,
                $this->getEntityManagerProviderMockMeta(
                    \stdClass::class,
                    $this->getClassMetadataMockIdentifiers([$a, 1, 1], [$b, 2, 2])
                ),
            ],
        ];
    }

    /**
     * @param mixed[][] $identifiers
     *
     * @return ClassMetadata&MockObject
     */
    private function getClassMetadataMockIdentifiers(...$identifiers): ClassMetadata
    {
        $meta = $this->getClassMetadataMock();

        $meta->method('getIdentifierValues')->willReturnCallback(
            function (object $toCheck) use ($identifiers): array {
                $return = [];
                foreach ($identifiers as $identifier) {
                    if (isset($identifier[0]) && $identifier[0] === $toCheck) {
                        $return = \array_slice($identifier, 1);
                    }
                }

                return $return;
            }
        );

        return $meta;
    }
}
