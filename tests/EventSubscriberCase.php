<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Marvin255\DoctrineTranslationBundle\ClassNameManager\ClassNameManager;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Base test case for event subscriber tests.
 */
abstract class EventSubscriberCase extends BaseCase
{
    protected function assertAssociationTarget(string $expected, LoadClassMetadataEventArgs $args, string $associationName): void
    {
        $associations = $args->getClassMetadata()->associationMappings;
        $this->assertSame($expected, $associations[$associationName]['targetEntity'] ?? '');
    }

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

    /**
     * @psalm-param array<string, string> $translatableTranslationPairs
     *
     * @psalm-suppress MixedPropertyTypeCoercion
     */
    protected function createClassNameManagerMock(array $translatableTranslationPairs = []): ClassNameManager
    {
        /** @var ClassNameManager&MockObject */
        $manager = $this->getMockBuilder(ClassNameManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager->method('isTranslationClass')->willReturnCallback(
            fn (string $toCheck): bool => \in_array($toCheck, $translatableTranslationPairs)
        );

        $manager->method('isTranslatableClass')->willReturnCallback(
            fn (string $toCheck): bool => \array_key_exists($toCheck, $translatableTranslationPairs)
        );

        $manager->method('getTranslationClassForTranslatable')->willReturnCallback(
            fn (string $translatable): string => $translatableTranslationPairs[$translatable] ?? ''
        );

        $manager->method('getTranslatableClassForTranslation')->willReturnCallback(
            fn (string $translation): string => array_search($translation, $translatableTranslationPairs) ?: ''
        );

        return $manager;
    }
}
