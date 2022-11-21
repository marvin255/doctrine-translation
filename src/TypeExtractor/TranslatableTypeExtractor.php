<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\TypeExtractor;

use Doctrine\Common\Collections\Collection;
use Marvin255\DoctrineTranslationBundle\ClassNameManager\ClassNameManager;
use Marvin255\DoctrineTranslationBundle\Entity\Translatable;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\PropertyInfo\Type;

/**
 * Type extractor which provide correct translation types for translatable
 * in "symfony/property-info".
 */
class TranslatableTypeExtractor implements PropertyTypeExtractorInterface
{
    private readonly ClassNameManager $classNameManager;

    public function __construct(ClassNameManager $classNameManager)
    {
        $this->classNameManager = $classNameManager;
    }

    /**
     * {@inheritDoc}
     *
     * @return Type[]|null
     */
    public function getTypes(string $class, string $property, array $context = [])
    {
        if (
            !\in_array($property, [Translatable::TRANSLATED_FIELD_NAME, Translatable::TRANSLATIONS_FIELD_NAME])
            || !$this->classNameManager->isTranslatableClass($class)
        ) {
            return null;
        }

        $translationClass = $this->classNameManager->getTranslationClassForTranslatable($class);
        $keyType = new Type(Type::BUILTIN_TYPE_INT, false);
        $valueType = new Type(Type::BUILTIN_TYPE_OBJECT, false, $translationClass);

        if ($property === Translatable::TRANSLATED_FIELD_NAME) {
            $type = new Type(
                Type::BUILTIN_TYPE_ARRAY,
                false,
                null,
                true,
                $keyType,
                $valueType
            );
        } else {
            $type = new Type(
                Type::BUILTIN_TYPE_OBJECT,
                false,
                Collection::class,
                true,
                $keyType,
                $valueType
            );
        }

        return [$type];
    }
}
