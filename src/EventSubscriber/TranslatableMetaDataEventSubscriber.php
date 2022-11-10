<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Marvin255\DoctrineTranslationBundle\ClassNameManager\ClassNameManager;
use Marvin255\DoctrineTranslationBundle\Entity\Translation;

/**
 * Event subscriber that allows dynamically change Doctrine mappings for translatables.
 */
final class TranslatableMetaDataEventSubscriber implements EventSubscriberInterface
{
    private readonly ClassNameManager $classNameManager;

    public function __construct(ClassNameManager $classNameManager)
    {
        $this->classNameManager = $classNameManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::loadClassMetadata,
        ];
    }

    /**
     * Cathes loadClassMetadata event and injects correct mapping for translations.
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $args): void
    {
        $metadata = $args->getClassMetadata();
        $this->fixAssociations($metadata);
    }

    /**
     * Fix all translations associations which have Translatable as target.
     */
    private function fixAssociations(ClassMetadata $metadata): void
    {
        $associations = $metadata->getAssociationMappings();
        foreach ($associations as $key => $association) {
            if (
                $this->classNameManager->isTranslatableClass($association['sourceEntity'])
                && $association['targetEntity'] === Translation::class
            ) {
                $targetEntity = $this->classNameManager->getTranslationClassForTranslatable($association['sourceEntity']);
                // it's a dirty hack, but there is no another way to update association
                $metadata->associationMappings[$key]['targetEntity'] = $targetEntity;
            }
        }
    }
}
