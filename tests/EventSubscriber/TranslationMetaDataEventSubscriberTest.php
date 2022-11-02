<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\EventSubscriber;

use Doctrine\ORM\Events;
use Marvin255\DoctrineTranslationBundle\Entity\Translatable;
use Marvin255\DoctrineTranslationBundle\Entity\Translation;
use Marvin255\DoctrineTranslationBundle\EventSubscriber\TranslationMetaDataEventSubscriber;
use Marvin255\DoctrineTranslationBundle\Exception\MappingException;
use Marvin255\DoctrineTranslationBundle\Tests\EventSubscriberCase;
use Marvin255\DoctrineTranslationBundle\Tests\Mock\MockNonTranslatableTranslation;
use Marvin255\DoctrineTranslationBundle\Tests\Mock\MockNoPairTranslation;
use Marvin255\DoctrineTranslationBundle\Tests\Mock\MockTranslatableItem;
use Marvin255\DoctrineTranslationBundle\Tests\Mock\MockTranslatableItemTranslation;
use Marvin255\DoctrineTranslationBundle\Tests\Mock\MockTranslationWrong;
use Throwable;

/**
 * @internal
 */
class TranslationMetaDataEventSubscriberTest extends EventSubscriberCase
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
        /** @var mixed[] */
        $table = $args->getClassMetadata()->table;

        $this->assertSame(
            [
                'uniqueConstraints' => [
                    $indexName => [
                        'columns' => [
                            Translation::LOCALE_COLUMN_NAME,
                            Translation::TRANSLATABLE_COLUMN_NAME,
                        ],
                    ],
                ],
            ],
            $table
        );
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

        $this->assertSame([], $table);
    }

    /**
     * @dataProvider provideLoadClassMetadataFixAssociations
     */
    public function testLoadClassMetadataFixAssociations(string $source, string $target, string|Throwable $result): void
    {
        $associationName = 'test';
        $args = $this->createEventArgsMock(
            [
                'associations' => [
                    $associationName => [
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
            $this->assertSame($result, $associations[$associationName]['targetEntity'] ?? '');
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
}
