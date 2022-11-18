<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\TypeExtractor;

use Marvin255\DoctrineTranslationBundle\ClassNameManager\ClassNameManager;
use Marvin255\DoctrineTranslationBundle\Entity\Translation;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\PropertyInfo\Type;

/**
 * Type extractor which provide correct translatable types for translation
 * in "symfony/property-info".
 */
class TranslationTypeExtractor implements PropertyTypeExtractorInterface
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
        if ($property !== Translation::TRANSLATABLE_FIELD_NAME || !$this->classNameManager->isTranslationClass($class)) {
            return null;
        }

        $translatableClass = $this->classNameManager->getTranslatableClassForTranslation($class);
        $valueType = new Type(Type::BUILTIN_TYPE_OBJECT, true, $translatableClass);

        return [$valueType];
    }
}
