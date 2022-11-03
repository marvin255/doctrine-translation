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
 * Event subscriber that allows dynamically change Doctrine mappings for translations.
 */
final class TranslationMetaDataEventSubscriber implements EventSubscriberInterface
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
        $this->addTranslationIndex($metadata);
    }

    /**
     * Adds new unique index on translation id and locale.
     */
    private function addTranslationIndex(ClassMetadata $metadata): void
    {
        if (!is_subclass_of($metadata->getName(), Translation::class)) {
            return;
        }

        $indexName = strtolower(str_replace('\\', '_', $metadata->getName())) . '_translation_idx';
        $metadata->table['uniqueConstraints'][$indexName] = [
            'columns' => [
                Translation::LOCALE_COLUMN_NAME,
                Translation::TRANSLATABLE_COLUMN_NAME,
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
                is_subclass_of($association['sourceEntity'], Translation::class)
                && $association['targetEntity'] === Translatable::class
            ) {
                $targetEntity = $this->createTranslatableClassName($association['sourceEntity']);
                // it's a dirty hack, but there is no another way to update association
                $metadata->associationMappings[$key]['targetEntity'] = $targetEntity;
            }
        }
    }

    /**
     * Creates class name for related translatable entity.
     *
     * @psalm-return class-string
     */
    private function createTranslatableClassName(string $sourceClassName): string
    {
        $suffix = Translation::TRANSLATION_CLASS_SUFFIX;
        if (!preg_match("/(.+){$suffix}$/", $sourceClassName, $matches)) {
            throw new MappingException("Entity '{$sourceClassName}' name must ends with '{$suffix}' suffix");
        }

        $className = $matches[1];

        if (!class_exists($className)) {
            throw new MappingException("Can't find '{$className}' for translation '{$sourceClassName}'");
        }

        if (!is_subclass_of($className, Translatable::class)) {
            $requiredType = Translatable::class;
            throw new MappingException("'{$className}' for translation '{$sourceClassName}' must extends '{$requiredType}'");
        }

        return $className;
    }
}
