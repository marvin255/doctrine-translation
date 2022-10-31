<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\EventSubscriber;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Marvin255\DoctrineTranslationBundle\EventSubscriber\TranslationMetaDataEventSubscriber;
use Marvin255\DoctrineTranslationBundle\Tests\BaseCase;
use PHPUnit\Framework\MockObject\MockObject;

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

    private function createEventArgsMock(string $sourceEntity, string $targetEntity): LoadClassMetadataEventArgs
    {
        /** @var ClassMetadata&MockObject */
        $metadata = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $metadata->method('getAssociationMappings')->willReturn(
            [
                [
                    'sourceEntity' => 'test',
                    'targetEntity' => 'test',
                ],
                [
                    'sourceEntity' => $sourceEntity,
                    'targetEntity' => $targetEntity,
                ],
                [
                    'sourceEntity' => 'test1',
                    'targetEntity' => 'test1',
                ],
            ],
        );

        /** @var LoadClassMetadataEventArgs&MockObject */
        $argsMock = $this->getMockBuilder(LoadClassMetadataEventArgs::class)->getMock();
        $argsMock->method('getClassMetadata')->willReturn($metadata);

        return $argsMock;
    }
}
