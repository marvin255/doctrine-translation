<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Marvin255\DoctrineTranslationBundle\Entity\Translatable;
use Marvin255\DoctrineTranslationBundle\Entity\Translation;
use Marvin255\DoctrineTranslationBundle\Exception\MappingException;

/**
 * Event subscriber that allows dynamically change Doctrine mappings for translatables.
 */
final class TranslatableMetaDataEventSubscriber implements EventSubscriberInterface
{
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
        foreach ($associations as $association) {
            if (
                is_subclass_of($association['sourceEntity'], Translatable::class)
                && $association['targetEntity'] === Translation::class
            ) {
                $targetEntity = $this->createTranslationClassName($association['sourceEntity']);
                // it's a dirty hack, but there is no another way to update association
                $metadata->associationMappings[$association['fieldName']]['targetEntity'] = $targetEntity;
            }
        }
    }

    /**
     * Creates class name for related translation entity.
     *
     * @psalm-return class-string
     */
    private function createTranslationClassName(string $sourceClassName): string
    {
        $className = "{$sourceClassName}Translation";

        if (!class_exists($className)) {
            throw new MappingException("Can't find '{$className}' for translatable '{$sourceClassName}'");
        }

        if (!is_subclass_of($className, Translation::class)) {
            $requiredType = Translation::class;
            throw new MappingException("'{$className}' for translatable '{$sourceClassName}' must extends '{$requiredType}'");
        }

        return $className;
    }
}
