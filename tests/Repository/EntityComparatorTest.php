<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\Repository;

use Marvin255\DoctrineTranslationBundle\Repository\EntityComparator;
use Marvin255\DoctrineTranslationBundle\Tests\EmCase;

/**
 * @internal
 */
class EntityComparatorTest extends EmCase
{
    /**
     * @dataProvider provideIsEqual
     */
    public function testIsEqual(mixed $a, mixed $b, bool $reference): void
    {
        $em = $this->createEmMock();

        $comparator = new EntityComparator($em);
        $res = $comparator->isEqual($a, $b);

        $this->assertSame($reference, $res);
    }

    public function provideIsEqual(): array
    {
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
        ];
    }

    public function testIsEqualByIds(): void
    {
        $ids = [1, 2, 3];
        $a = new \stdClass();
        $b = new \stdClass();

        $meta = $this->createMetaMock(
            [
                [$a, $ids],
                [$b, $ids],
            ]
        );

        $em = $this->createEmMock([\get_class($a) => $meta]);

        $comparator = new EntityComparator($em);
        $res = $comparator->isEqual($a, $b);

        $this->assertTrue($res);
    }

    public function testIsNotEqualByIds(): void
    {
        $a = new \stdClass();
        $b = new \stdClass();

        $meta = $this->createMetaMock(
            [
                [$a, [1]],
                [$b, [2]],
            ]
        );

        $em = $this->createEmMock([\get_class($a) => $meta]);

        $comparator = new EntityComparator($em);
        $res = $comparator->isEqual($a, $b);

        $this->assertFalse($res);
    }
}
