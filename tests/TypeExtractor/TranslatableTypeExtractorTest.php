<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\TypeExtractor;

use Doctrine\Common\Collections\Collection;
use Marvin255\DoctrineTranslationBundle\Entity\Translatable;
use Marvin255\DoctrineTranslationBundle\Tests\BaseCase;
use Marvin255\DoctrineTranslationBundle\TypeExtractor\TranslatableTypeExtractor;
use Symfony\Component\PropertyInfo\Type;

/**
 * @internal
 */
class TranslatableTypeExtractorTest extends BaseCase
{
    public function testGetTypesWrongPropertyName(): void
    {
        $classNameTranslatable = 'testTranslatable';
        $classNameTranslation = 'testTranslation';
        $classNameManager = $this->createClassNameManagerMock([$classNameTranslatable => $classNameTranslation]);

        $extractor = new TranslatableTypeExtractor($classNameManager);
        $res = $extractor->getTypes($classNameTranslatable, 'property');

        $this->assertNull($res);
    }

    public function testGetTypesWrongClassName(): void
    {
        $classNameManager = $this->createClassNameManagerMock();

        $extractor = new TranslatableTypeExtractor($classNameManager);
        $res = $extractor->getTypes('testClass', Translatable::TRANSLATIONS_FIELD_NAME);

        $this->assertNull($res);
    }

    public function testGetTypeTranslatedField(): void
    {
        $classNameTranslatable = 'testTranslatable';
        $classNameTranslation = 'testTranslation';
        $classNameManager = $this->createClassNameManagerMock([$classNameTranslatable => $classNameTranslation]);

        $extractor = new TranslatableTypeExtractor($classNameManager);
        $res = $extractor->getTypes($classNameTranslatable, Translatable::TRANSLATED_FIELD_NAME);

        $this->assertIsArray($res);
        $this->assertCount(1, $res);
        $this->assertInstanceOf(Type::class, $res[0]);
        $this->assertSame(Type::BUILTIN_TYPE_ARRAY, $res[0]->getBuiltinType());
        $this->assertFalse($res[0]->isNullable());
        $this->assertTrue($res[0]->isCollection());
        $this->assertNull($res[0]->getClassName());

        $this->assertIsArray($res[0]->getCollectionKeyTypes());
        $this->assertCount(1, $res[0]->getCollectionKeyTypes());
        $this->assertInstanceOf(Type::class, $res[0]->getCollectionKeyTypes()[0]);
        $this->assertSame(Type::BUILTIN_TYPE_INT, $res[0]->getCollectionKeyTypes()[0]->getBuiltinType());
        $this->assertFalse($res[0]->getCollectionKeyTypes()[0]->isNullable());

        $this->assertIsArray($res[0]->getCollectionValueTypes());
        $this->assertCount(1, $res[0]->getCollectionValueTypes());
        $this->assertInstanceOf(Type::class, $res[0]->getCollectionValueTypes()[0]);
        $this->assertSame($classNameTranslation, $res[0]->getCollectionValueTypes()[0]->getClassName());
        $this->assertFalse($res[0]->getCollectionValueTypes()[0]->isNullable());
    }

    public function testGetTypeTranslations(): void
    {
        $classNameTranslatable = 'testTranslatable';
        $classNameTranslation = 'testTranslation';
        $classNameManager = $this->createClassNameManagerMock([$classNameTranslatable => $classNameTranslation]);

        $extractor = new TranslatableTypeExtractor($classNameManager);
        $res = $extractor->getTypes($classNameTranslatable, Translatable::TRANSLATIONS_FIELD_NAME);

        $this->assertIsArray($res);
        $this->assertCount(1, $res);
        $this->assertInstanceOf(Type::class, $res[0]);
        $this->assertSame(Type::BUILTIN_TYPE_OBJECT, $res[0]->getBuiltinType());
        $this->assertFalse($res[0]->isNullable());
        $this->assertTrue($res[0]->isCollection());
        $this->assertSame(Collection::class, $res[0]->getClassName());

        $this->assertIsArray($res[0]->getCollectionKeyTypes());
        $this->assertCount(1, $res[0]->getCollectionKeyTypes());
        $this->assertInstanceOf(Type::class, $res[0]->getCollectionKeyTypes()[0]);
        $this->assertSame(Type::BUILTIN_TYPE_INT, $res[0]->getCollectionKeyTypes()[0]->getBuiltinType());
        $this->assertFalse($res[0]->getCollectionKeyTypes()[0]->isNullable());

        $this->assertIsArray($res[0]->getCollectionValueTypes());
        $this->assertCount(1, $res[0]->getCollectionValueTypes());
        $this->assertInstanceOf(Type::class, $res[0]->getCollectionValueTypes()[0]);
        $this->assertSame($classNameTranslation, $res[0]->getCollectionValueTypes()[0]->getClassName());
        $this->assertFalse($res[0]->getCollectionValueTypes()[0]->isNullable());
    }
}
