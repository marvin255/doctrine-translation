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
        if (!$this->classNameManager->isTranslatableClass($class)) {
            return null;
        }

        $translationClass = $this->classNameManager->getTranslationClassForTranslatable($class);

        $type = null;
        if ($property === Translatable::CURRENT_TRANSLATION_FIELD_NAME) {
            $type = [
                new Type(Type::BUILTIN_TYPE_OBJECT, false, $translationClass),
            ];
        } elseif ($property === Translatable::TRANSLATIONS_FIELD_NAME) {
            $keyType = new Type(Type::BUILTIN_TYPE_INT, false);
            $valueType = new Type(Type::BUILTIN_TYPE_OBJECT, false, $translationClass);
            $type = [
                new Type(
                    Type::BUILTIN_TYPE_OBJECT,
                    false,
                    Collection::class,
                    true,
                    $keyType,
                    $valueType
                ),
            ];
        }

        return $type;
    }
}
