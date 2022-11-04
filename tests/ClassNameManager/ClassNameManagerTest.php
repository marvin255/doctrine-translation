<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\ClassNameManager;

use Marvin255\DoctrineTranslationBundle\ClassNameManager\ClassNameManager;
use Marvin255\DoctrineTranslationBundle\Exception\MappingException;
use Marvin255\DoctrineTranslationBundle\Tests\BaseCase;
use Marvin255\DoctrineTranslationBundle\Tests\Mock\MockNonTranslatableTranslation;
use Marvin255\DoctrineTranslationBundle\Tests\Mock\MockNoPairTranslation;
use Marvin255\DoctrineTranslationBundle\Tests\Mock\MockTranslatableItem;
use Marvin255\DoctrineTranslationBundle\Tests\Mock\MockTranslatableItemTranslation;

/**
 * @internal
 */
class ClassNameManagerTest extends BaseCase
{
    /**
     * @dataProvider provideIsTranslationClass
     */
    public function testIsTranslationClass(string $className, bool $reference): void
    {
        $manager = new ClassNameManager();
        $isClass = $manager->isTranslationClass($className);

        $this->assertSame($reference, $isClass);
    }

    public function provideIsTranslationClass(): array
    {
        return [
            'correct class' => [
                MockTranslatableItemTranslation::class,
                true,
            ],
            'incorrect class' => [
                self::class,
                false,
            ],
        ];
    }

    /**
     * @dataProvider provideIsTranslatableClass
     */
    public function testIsTranslatableClass(string $className, bool $reference): void
    {
        $manager = new ClassNameManager();
        $isClass = $manager->isTranslatableClass($className);

        $this->assertSame($reference, $isClass);
    }

    public function provideIsTranslatableClass(): array
    {
        return [
            'correct class' => [
                MockTranslatableItem::class,
                true,
            ],
            'incorrect class' => [
                self::class,
                false,
            ],
        ];
    }

    /**
     * @dataProvider provideGetTranslationClassForTranslatable
     */
    public function testGetTranslationClassForTranslatable(string $className, string $reference): void
    {
        $manager = new ClassNameManager();
        $class = $manager->getTranslationClassForTranslatable($className);

        $this->assertSame($reference, $class);
    }

    public function provideGetTranslationClassForTranslatable(): array
    {
        return [
            'correct class' => [
                MockTranslatableItem::class,
                MockTranslatableItemTranslation::class,
            ],
        ];
    }

    /**
     * @dataProvider provideGetTranslatableClassForTranslation
     */
    public function testGetTranslatableClassForTranslation(string $className, string|\Throwable $reference): void
    {
        $manager = new ClassNameManager();

        if ($reference instanceof \Throwable) {
            $this->expectException(\get_class($reference));
            $this->expectDeprecationMessage($reference->getMessage());
        }

        $class = $manager->getTranslatableClassForTranslation($className);

        if (!($reference instanceof \Throwable)) {
            $this->assertSame($reference, $class);
        }
    }

    public function provideGetTranslatableClassForTranslation(): array
    {
        return [
            'correct class' => [
                MockTranslatableItemTranslation::class,
                MockTranslatableItem::class,
            ],
            'not a class' => [
                '_qwe_qwe',
                new MappingException("doesn't exist"),
            ],
            'translation class without suffix' => [
                self::class,
                new MappingException('must end with'),
            ],
            'translation without translatable pair' => [
                MockNoPairTranslation::class,
                new MappingException("Can't find"),
            ],
            'translatable with incorrect parent' => [
                MockNonTranslatableTranslation::class,
                new MappingException('must extends'),
            ],
        ];
    }
}
