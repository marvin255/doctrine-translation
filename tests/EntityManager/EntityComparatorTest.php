<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\EntityManager;

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
        $em = $em ?: $this->createEmProviderMetaMock();

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
                new \stdClass(),
                false,
            ],
            'equal by id' => [
                $a,
                $b,
                true,
                $this->createEmProviderMetaMock(
                    [
                        \get_class($a) => [
                            [$a, [1, 2]],
                            [$b, [1, 2]],
                        ],
                    ]
                ),
            ],
            'don\'t equal by id' => [
                $a,
                $b,
                false,
                $this->createEmProviderMetaMock(
                    [
                        \get_class($a) => [
                            [$a, [1]],
                            [$b, [2]],
                        ],
                    ]
                ),
            ],
        ];
    }
}
