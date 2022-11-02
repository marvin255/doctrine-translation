<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\EventSubscriber;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Marvin255\DoctrineTranslationBundle\Entity\Translatable;
use Marvin255\DoctrineTranslationBundle\Entity\Translation;
use Marvin255\DoctrineTranslationBundle\EventSubscriber\TranslationMetaDataEventSubscriber;
use Marvin255\DoctrineTranslationBundle\Exception\MappingException;
use Marvin255\DoctrineTranslationBundle\Tests\BaseCase;
use Marvin255\DoctrineTranslationBundle\Tests\Mock\MockNonTranslatableTranslation;
use Marvin255\DoctrineTranslationBundle\Tests\Mock\MockNoPairTranslation;
use Marvin255\DoctrineTranslationBundle\Tests\Mock\MockTranslatableItem;
use Marvin255\DoctrineTranslationBundle\Tests\Mock\MockTranslatableItemTranslation;
use Marvin255\DoctrineTranslationBundle\Tests\Mock\MockTranslationWrong;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

/**
 * @internal
 */
class TranslationMetaDataEventSubscriberTest extends BaseCase
{
    public function testGetSubscribedEvents(): void
    {
        $subscriber = new TranslationMetaDataEventSubscriber();
        $events = $subscriber->getSubscribedEvents();

        $this->assertSame([Events::loadClassMetadata], $events);
    }

    public function testLoadClassMetadataCreateIndex(): void
    {
        $name = MockTranslatableItemTranslation::class;
        $indexName = strtolower(str_replace('\\', '_', $name)) . '_translation_idx';
        $args = $this->createEventArgsMock(
            [
                'name' => $name,
            ]
        );

        $subscriber = new TranslationMetaDataEventSubscriber();
        $subscriber->loadClassMetadata($args);
        $table = $args->getClassMetadata()->table;

        $this->assertArrayHasKey('uniqueConstraints', $table);
        $this->assertArrayHasKey($indexName, $table['uniqueConstraints']);
        $this->assertArrayHasKey('columns', $table['uniqueConstraints'][$indexName]);
        $this->assertContains(Translation::LOCALE_COLUMN_NAME, $table['uniqueConstraints'][$indexName]['columns']);
        $this->assertContains(Translation::TRANSLATABLE_COLUMN_NAME, $table['uniqueConstraints'][$indexName]['columns']);
    }

    public function testLoadClassMetadataDontCreateIndexForNonTranslation(): void
    {
        $name = 'test';
        $args = $this->createEventArgsMock(
            [
                'name' => $name,
            ]
        );

        $subscriber = new TranslationMetaDataEventSubscriber();
        $subscriber->loadClassMetadata($args);
        $table = $args->getClassMetadata()->table;

        $this->assertArrayNotHasKey('uniqueConstraints', $table);
    }

    /**
     * @dataProvider provideLoadClassMetadataFixAssociations
     */
    public function testLoadClassMetadataFixAssociations(string $source, string $target, string|Throwable $result): void
    {
        $args = $this->createEventArgsMock(
            [
                'associations' => [
                    [
                        'sourceEntity' => $source,
                        'targetEntity' => $target,
                    ],
                ],
            ]
        );

        $subscriber = new TranslationMetaDataEventSubscriber();

        if ($result instanceof Throwable) {
            $this->expectException(\get_class($result));
            $this->expectExceptionMessage($result->getMessage());
        }

        $subscriber->loadClassMetadata($args);
        $associations = $args->getClassMetadata()->associationMappings;

        if (!($result instanceof Throwable)) {
            $this->assertSame($result, $associations[0]['targetEntity']);
        }
    }

    public function provideLoadClassMetadataFixAssociations(): array
    {
        return [
            'Translation - Translatable association must be fixed' => [
                MockTranslatableItemTranslation::class,
                Translatable::class,
                MockTranslatableItem::class,
            ],
            "Association with wrong source class mustn't be fixed" => [
                self::class,
                Translatable::class,
                Translatable::class,
            ],
            "Association with wrong target class mustn't be fixed" => [
                MockTranslatableItemTranslation::class,
                self::class,
                self::class,
            ],
            'Translation class without suffix' => [
                MockTranslationWrong::class,
                Translatable::class,
                new MappingException('name must ends with'),
            ],
            'Translation without translatable pair' => [
                MockNoPairTranslation::class,
                Translatable::class,
                new MappingException("Can't find"),
            ],
            'Translatable with incorrect parent' => [
                MockNonTranslatableTranslation::class,
                Translatable::class,
                new MappingException('must extends'),
            ],
        ];
    }

    private function createEventArgsMock(array $data = []): LoadClassMetadataEventArgs
    {
        /** @var ClassMetadata&MockObject */
        $metadata = $this->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadata->method('getName')->willReturn($data['name'] ?? '');
        $metadata->method('getAssociationMappings')->willReturn($data['associations'] ?? []);
        $metadata->associationMappings = $data['associations'] ?? [];
        $metadata->table = $data['table'] ?? [];

        /** @var LoadClassMetadataEventArgs&MockObject */
        $argsMock = $this->getMockBuilder(LoadClassMetadataEventArgs::class)
            ->disableOriginalConstructor()
            ->getMock();
        $argsMock->method('getClassMetadata')->willReturn($metadata);

        return $argsMock;
    }
}
