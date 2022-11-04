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
     */
    public function getTranslationClassForTranslatable(string $translatableClass): string
    {
        return $translatableClass . self::TRANSLATION_CLASS_SUFFIX;
    }

    /**
     * Returns class name for translation related to set translatable.
     */
    public function getTranslatableClassForTranslation(string $translationClass): string
    {
        $suffix = self::TRANSLATION_CLASS_SUFFIX;
        if (!preg_match("/(.+){$suffix}$/", $translationClass, $matches)) {
            throw new MappingException("Class name '{$translationClass}' must end with '{$suffix}' suffix");
        }

        return $matches[1];
    }
}
