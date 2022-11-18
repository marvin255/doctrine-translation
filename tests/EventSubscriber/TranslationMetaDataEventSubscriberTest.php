<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\EventSubscriber;

use Doctrine\ORM\Events;
use Marvin255\DoctrineTranslationBundle\Entity\Translatable;
use Marvin255\DoctrineTranslationBundle\Entity\Translation;
use Marvin255\DoctrineTranslationBundle\EventSubscriber\TranslationMetaDataEventSubscriber;
use Marvin255\DoctrineTranslationBundle\Tests\EventSubscriberCase;

/**
 * @internal
 */
class TranslationMetaDataEventSubscriberTest extends EventSubscriberCase
{
    public function testGetSubscribedEvents(): void
    {
        $classNameManager = $this->createClassNameManagerMock();

        $subscriber = new TranslationMetaDataEventSubscriber($classNameManager);
        $events = $subscriber->getSubscribedEvents();

        $this->assertSame([Events::loadClassMetadata], $events);
    }

    public function testLoadClassMetadataCreateIndex(): void
    {
        $name = 'itemName';
        $indexName = strtolower(str_replace('\\', '_', $name)) . '_translation_idx';
        $classNameManager = $this->createClassNameManagerMock(['Translatable' => $name]);
        $args = $this->createEventArgsMock(['name' => $name]);

        $subscriber = new TranslationMetaDataEventSubscriber($classNameManager);
        $subscriber->loadClassMetadata($args);

        $this->assertSame(
            [
                'uniqueConstraints' => [
                    $indexName => [
                        'columns' => [
                            Translation::TRANSLATABLE_COLUMN_NAME,
                            Translation::LOCALE_COLUMN_NAME,
                        ],
                    ],
                ],
            ],
            $args->getClassMetadata()->table
        );
    }

    public function testLoadClassMetadataDontCreateIndexForNonTranslation(): void
    {
        $classNameManager = $this->createClassNameManagerMock();
        $args = $this->createEventArgsMock(['name' => 'test_entity_name']);

        $subscriber = new TranslationMetaDataEventSubscriber($classNameManager);
        $subscriber->loadClassMetadata($args);
        $table = $args->getClassMetadata()->table;

        $this->assertSame([], $table);
    }

    public function testLoadClassMetadataFixAssociations(): void
    {
        $associationName = 'correct_association';
        $associationTranslation = 'Translation';
        $associationTranslatable = 'Translatable';

        $association1Name = 'wrong_target_association';
        $association1Translation = 'Translation1';
        $association1Translatable = 'Translatable1';

        $association2Name = 'wrong_source_association';

        $classNameManager = $this->createClassNameManagerMock(
            [
                $associationTranslatable => $associationTranslation,
                $association1Translatable => $association1Translation,
            ]
        );
        $args = $this->createEventArgsMock(
            [
                'associations' => [
                    $associationName => [
                        'sourceEntity' => $associationTranslation,
                        'targetEntity' => Translatable::class,
                    ],
                    $association1Name => [
                        'sourceEntity' => $association1Translation,
                        'targetEntity' => self::class,
                    ],
                    $association2Name => [
                        'sourceEntity' => 'non_existed_source',
                        'targetEntity' => Translatable::class,
                    ],
                ],
            ]
        );

        $subscriber = new TranslationMetaDataEventSubscriber($classNameManager);
        $subscriber->loadClassMetadata($args);

        $this->assertAssociationTarget($associationTranslatable, $args, $associationName);
        $this->assertAssociationTarget(self::class, $args, $association1Name);
        $this->assertAssociationTarget(Translatable::class, $args, $association2Name);
    }
}
