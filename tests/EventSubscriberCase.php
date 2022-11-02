<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Base test case for event subscriber tests.
 */
abstract class EventSubscriberCase extends BaseCase
{
    /**
     * @psalm-suppress MixedPropertyTypeCoercion
     */
    protected function createEventArgsMock(array $data = []): LoadClassMetadataEventArgs
    {
        $name = (string) ($data['name'] ?? '');
        $table = (array) ($data['table'] ?? []);
        $associations = (array) ($data['associations'] ?? []);

        /** @var ClassMetadata&MockObject */
        $metadata = $this->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->method('getName')->willReturn($name);
        $metadata->method('getAssociationMappings')->willReturn($associations);
        $metadata->associationMappings = $associations;
        $metadata->table = $table;

        /** @var LoadClassMetadataEventArgs&MockObject */
        $argsMock = $this->getMockBuilder(LoadClassMetadataEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
        $argsMock->method('getClassMetadata')->willReturn($metadata);

        return $argsMock;
    }
}
