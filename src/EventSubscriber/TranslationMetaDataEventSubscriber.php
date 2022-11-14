<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Marvin255\DoctrineTranslationBundle\ClassNameManager\ClassNameManager;
use Marvin255\DoctrineTranslationBundle\Entity\Translatable;
use Marvin255\DoctrineTranslationBundle\Entity\Translation;

/**
 * Event subscriber that allows dynamically change Doctrine mappings for translations.
 */
final class TranslationMetaDataEventSubscriber implements EventSubscriberInterface
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
        $this->addTranslationIndex($metadata);
    }

    /**
     * Adds new unique index on translation id and locale.
     */
    private function addTranslationIndex(ClassMetadata $metadata): void
    {
        if (!$this->classNameManager->isTranslationClass($metadata->getName())) {
            return;
        }

        $indexName = strtolower(str_replace('\\', '_', $metadata->getName())) . '_translation_idx';
        $metadata->table['uniqueConstraints'][$indexName] = [
            'columns' => [
                Translation::TRANSLATABLE_COLUMN_NAME,
                Translation::LOCALE_COLUMN_NAME,
            ],
        ];
    }

    /**
     * Fixes all translations associations which have Translatable as target.
     */
    private function fixAssociations(ClassMetadata $metadata): void
    {
        $associations = $metadata->getAssociationMappings();
        foreach ($associations as $key => $association) {
            if (
                $this->classNameManager->isTranslationClass($association['sourceEntity'])
                && $association['targetEntity'] === Translatable::class
            ) {
                $targetEntity = $this->classNameManager->getTranslatableClassForTranslation($association['sourceEntity']);
                // it's a dirty hack, but there is no another way to update association
                $metadata->associationMappings[$key]['targetEntity'] = $targetEntity;
            }
        }
    }
}
