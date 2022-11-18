<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\TypeExtractor;

use Marvin255\DoctrineTranslationBundle\Entity\Translation;
use Marvin255\DoctrineTranslationBundle\Tests\BaseCase;
use Marvin255\DoctrineTranslationBundle\TypeExtractor\TranslationTypeExtractor;
use Symfony\Component\PropertyInfo\Type;

/**
 * @internal
 */
class TranslationTypeExtractorTest extends BaseCase
{
    public function testGetTypesWrongPropertyName(): void
    {
        $classNameTranslatable = 'testTranslatable';
        $classNameTranslation = 'testTranslation';
        $classNameManager = $this->createClassNameManagerMock([$classNameTranslatable => $classNameTranslation]);

        $extractor = new TranslationTypeExtractor($classNameManager);
        $res = $extractor->getTypes($classNameTranslation, 'property');

        $this->assertNull($res);
    }

    public function testGetTypesWrongClassName(): void
    {
        $classNameManager = $this->createClassNameManagerMock();

        $extractor = new TranslationTypeExtractor($classNameManager);
        $res = $extractor->getTypes('testClass', Translation::TRANSLATABLE_FIELD_NAME);

        $this->assertNull($res);
    }

    public function testGetType(): void
    {
        $classNameTranslatable = 'testTranslatable';
        $classNameTranslation = 'testTranslation';
        $classNameManager = $this->createClassNameManagerMock([$classNameTranslatable => $classNameTranslation]);

        $extractor = new TranslationTypeExtractor($classNameManager);
        $res = $extractor->getTypes($classNameTranslation, Translation::TRANSLATABLE_FIELD_NAME);

        $this->assertIsArray($res);
        $this->assertCount(1, $res);
        $this->assertInstanceOf(Type::class, $res[0]);
        $this->assertSame(Type::BUILTIN_TYPE_OBJECT, $res[0]->getBuiltinType());
        $this->assertTrue($res[0]->isNullable());
        $this->assertSame($classNameTranslatable, $res[0]->getClassName());
    }
}
