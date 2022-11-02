<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\EventSubscriber;

use Doctrine\ORM\Events;
use Marvin255\DoctrineTranslationBundle\EventSubscriber\TranslatableMetaDataEventSubscriber;
use Marvin255\DoctrineTranslationBundle\Tests\EventSubscriberCase;

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
}
