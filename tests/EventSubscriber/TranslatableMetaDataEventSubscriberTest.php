<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\EventSubscriber;

use Doctrine\ORM\Events;
use Marvin255\DoctrineTranslationBundle\Entity\Translation;
use Marvin255\DoctrineTranslationBundle\EventSubscriber\TranslatableMetaDataEventSubscriber;
use Marvin255\DoctrineTranslationBundle\Tests\EventSubscriberCase;

/**
 * @internal
 */
class TranslatableMetaDataEventSubscriberTest extends EventSubscriberCase
{
    public function testGetSubscribedEvents(): void
    {
        $classNameManager = $this->createClassNameManagerMock();

        $subscriber = new TranslatableMetaDataEventSubscriber($classNameManager);
        $events = $subscriber->getSubscribedEvents();

        $this->assertSame([Events::loadClassMetadata], $events);
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
                        'sourceEntity' => $associationTranslatable,
                        'targetEntity' => Translation::class,
                    ],
                    $association1Name => [
                        'sourceEntity' => $association1Translatable,
                        'targetEntity' => self::class,
                    ],
                    $association2Name => [
                        'sourceEntity' => 'non_existed_source',
                        'targetEntity' => Translation::class,
                    ],
                ],
            ]
        );

        $subscriber = new TranslatableMetaDataEventSubscriber($classNameManager);
        $subscriber->loadClassMetadata($args);

        $this->assertAssociationTarget($associationTranslation, $args, $associationName);
        $this->assertAssociationTarget(self::class, $args, $association1Name);
        $this->assertAssociationTarget(Translation::class, $args, $association2Name);
    }
}
