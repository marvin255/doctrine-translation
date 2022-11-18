<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests;

use Marvin255\DoctrineTranslationBundle\ClassNameManager\ClassNameManager;
use Marvin255\DoctrineTranslationBundle\Locale\Locale;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Base test case for all tests.
 */
abstract class BaseCase extends TestCase
{
    protected function createLocaleMock(string $localeString = ''): Locale
    {
        /** @var Locale&MockObject */
        $locale = $this->getMockBuilder(Locale::class)
            ->disableOriginalConstructor()
            ->getMock();

        $locale->method('getFull')->willReturn($localeString);

        return $locale;
    }

    /**
     * @psalm-param array<string, string> $translatableTranslationPairs
     *
     * @psalm-suppress MixedPropertyTypeCoercion
     */
    protected function createClassNameManagerMock(array $translatableTranslationPairs = []): ClassNameManager
    {
        /** @var ClassNameManager&MockObject */
        $manager = $this->getMockBuilder(ClassNameManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manager->method('isTranslationClass')->willReturnCallback(
            fn (string $toCheck): bool => \in_array($toCheck, $translatableTranslationPairs)
        );

        $manager->method('isTranslatableClass')->willReturnCallback(
            fn (string $toCheck): bool => \array_key_exists($toCheck, $translatableTranslationPairs)
        );

        $manager->method('getTranslationClassForTranslatable')->willReturnCallback(
            fn (string $translatable): string => $translatableTranslationPairs[$translatable] ?? ''
        );

        $manager->method('getTranslatableClassForTranslation')->willReturnCallback(
            fn (string $translation): string => array_search($translation, $translatableTranslationPairs) ?: ''
        );

        $manager->method('getTranslationClassForTranslatableEntity')->willReturnCallback(
            fn (object $entity): string => $translatableTranslationPairs[\get_class($entity)] ?? ''
        );

        return $manager;
    }
}
