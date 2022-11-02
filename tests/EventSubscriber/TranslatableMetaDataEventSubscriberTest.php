<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\EventSubscriber;

use Doctrine\ORM\Events;
use Marvin255\DoctrineTranslationBundle\Entity\Translation;
use Marvin255\DoctrineTranslationBundle\EventSubscriber\TranslatableMetaDataEventSubscriber;
use Marvin255\DoctrineTranslationBundle\Exception\MappingException;
use Marvin255\DoctrineTranslationBundle\Tests\EventSubscriberCase;
use Marvin255\DoctrineTranslationBundle\Tests\Mock\MockNonTranslation;
use Marvin255\DoctrineTranslationBundle\Tests\Mock\MockNoPairTranslatable;
use Marvin255\DoctrineTranslationBundle\Tests\Mock\MockTranslatableItem;
use Marvin255\DoctrineTranslationBundle\Tests\Mock\MockTranslatableItemTranslation;

/**
 * @internal
 */
class TranslatableMetaDataEventSubscriberTest extends EventSubscriberCase
{
    public function testGetSubscribedEvents(): void
    {
        $subscriber = new TranslatableMetaDataEventSubscriber();
        $events = $subscriber->getSubscribedEvents();

        $this->assertSame([Events::loadClassMetadata], $events);
    }

    /**
     * @dataProvider provideLoadClassMetadataFixAssociations
     */
    public function testLoadClassMetadataFixAssociations(string $source, string $target, string|\Throwable $result): void
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

        $subscriber = new TranslatableMetaDataEventSubscriber();

        if ($result instanceof \Throwable) {
            $this->expectException(\get_class($result));
            $this->expectExceptionMessage($result->getMessage());
        }

        $subscriber->loadClassMetadata($args);

        if (!($result instanceof \Throwable)) {
            $this->assertAssociationTarget($result, $args, $associationName);
        }
    }

    public function provideLoadClassMetadataFixAssociations(): array
    {
        return [
            'Translatable - Translation association must be fixed' => [
                MockTranslatableItem::class,
                Translation::class,
                MockTranslatableItemTranslation::class,
            ],
            'Wrong target class' => [
                MockTranslatableItem::class,
                self::class,
                self::class,
            ],
            'No pair translatable' => [
                MockNoPairTranslatable::class,
                Translation::class,
                new MappingException("Can't find"),
            ],
            'Non translation class translation' => [
                MockNonTranslation::class,
                Translation::class,
                new MappingException('must extends'),
            ],
        ];
    }
}
