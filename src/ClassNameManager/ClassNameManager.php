<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\ClassNameManager;

use Marvin255\DoctrineTranslationBundle\Entity\Translatable;
use Marvin255\DoctrineTranslationBundle\Entity\Translation;
use Marvin255\DoctrineTranslationBundle\Exception\MappingException;

/**
 * Converts class names from translation to translatable and backward.
 */
class ClassNameManager
{
    private const TRANSLATION_CLASS_SUFFIX = 'Translation';

    /**
     * Check if set class name is a class name for translation item.
     */
    public function isTranslationClass(string $class): bool
    {
        return is_subclass_of($class, Translation::class);
    }

    /**
     * Check if set class name is a class name for translatable item.
     */
    public function isTranslatableClass(string $class): bool
    {
        return is_subclass_of($class, Translatable::class);
    }

    /**
     * Returns class name for translation related to set translatable.
     *
     * @psalm-return class-string
     */
    public function getTranslationClassForTranslatable(string $translatableClass): string
    {
        if (!class_exists($translatableClass)) {
            throw new MappingException("Class '{$translatableClass}' doesn't exist");
        }

        $className = $translatableClass . self::TRANSLATION_CLASS_SUFFIX;

        if (!class_exists($className)) {
            throw new MappingException("Can't find '{$className}' for translatable '{$translatableClass}'");
        }

        if (!is_subclass_of($className, Translation::class)) {
            $requiredType = Translation::class;
            throw new MappingException("'{$className}' for translatable '{$translatableClass}' must extend '{$requiredType}'");
        }

        return $className;
    }

    /**
     * Returns class name for translation related to set translatable.
     *
     * @psalm-return class-string
     */
    public function getTranslatableClassForTranslation(string $translationClass): string
    {
        if (!class_exists($translationClass)) {
            throw new MappingException("Class '{$translationClass}' doesn't exist");
        }

        $suffix = self::TRANSLATION_CLASS_SUFFIX;
        if (!preg_match("/(.+){$suffix}$/", $translationClass, $matches)) {
            throw new MappingException("Class name '{$translationClass}' must end with '{$suffix}' suffix");
        }

        $className = $matches[1];

        if (!class_exists($className)) {
            throw new MappingException("Can't find '{$className}' for translation '{$translationClass}'");
        }

        if (!$this->isTranslatableClass($className)) {
            $requiredType = Translatable::class;
            throw new MappingException("'{$className}' for translation '{$translationClass}' must extends '{$requiredType}'");
        }

        return $className;
    }
}
