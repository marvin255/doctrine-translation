<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Marvin255\DoctrineTranslationBundle\Repository\EntityComparator;
use Marvin255\DoctrineTranslationBundle\Tests\BaseCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 */
class EntityComparatorTest extends BaseCase
{
    public function testIsEqualSameObject(): void
    {
        $em = $this->createEmMock();

        $comparator = new EntityComparator($em);
        $res = $comparator->isEqual($this, $this);

        $this->assertTrue($res);
    }

    public function testIsEqualDifferentClasses(): void
    {
        $em = $this->createEmMock();

        $comparator = new EntityComparator($em);
        $res = $comparator->isEqual($this, new \stdClass());

        $this->assertFalse($res);
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

        $em = $this->createEmMock(\get_class($a), $meta);

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

        $em = $this->createEmMock(\get_class($a), $meta);

        $comparator = new EntityComparator($em);
        $res = $comparator->isEqual($a, $b);

        $this->assertFalse($res);
    }

    /**
     * @psalm-param array<int, mixed[]> $data
     */
    private function createMetaMock(array $data = []): ClassMetadata
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

    private function createEmMock(?string $class = null, ?ClassMetadata $meta = null): EntityManagerInterface
    {
        /** @var EntityManagerInterface&MockObject */
        $em = $this->getMockBuilder(EntityManagerInterface::class)->getMock();

        $em->method('getClassMetadata')->willReturnCallback(
            fn (string $toCheck): ?ClassMetadata => $toCheck === $class ? $meta : null
        );

        return $em;
    }
}
