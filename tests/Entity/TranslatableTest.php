<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\Entity;

use Marvin255\DoctrineTranslationBundle\Entity\Translatable;
use Marvin255\DoctrineTranslationBundle\Entity\Translation;
use Marvin255\DoctrineTranslationBundle\Tests\BaseCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 */
class TranslatableTest extends BaseCase
{
    public function testAddTranslation(): void
    {
        /** @var Translation */
        $translation = $this->getMockBuilder(Translation::class)->getMock();

        /** @var Translatable */
        $model = $this->getMockForAbstractClass(Translatable::class);

        $this->assertSame($model, $model->addTranslation($translation));
        $this->assertSame([$translation], $model->getTranslations());
    }

    public function testClearTranslations(): void
    {
        /** @var Translation */
        $translation = $this->getMockBuilder(Translation::class)->getMock();
        /** @var Translation */
        $translation1 = $this->getMockBuilder(Translation::class)->getMock();

        /** @var Translatable */
        $model = $this->getMockForAbstractClass(Translatable::class);
        $model->addTranslation($translation);
        $model->addTranslation($translation1);

        $this->assertSame($model, $model->clearTranslations());
        $this->assertSame([], $model->getTranslations());
    }

    public function testRemoveTranslation(): void
    {
        /** @var Translation */
        $translation = $this->getMockBuilder(Translation::class)->getMock();

        /** @var Translatable */
        $model = $this->getMockForAbstractClass(Translatable::class);

        $this->assertSame($model, $model->addTranslation($translation));
        $this->assertSame($model, $model->removeTranslation($translation));
        $this->assertSame([], $model->getTranslations());
    }

    public function testFindTranslationByLocale(): void
    {
        $localeString = 'en-US';
        $localeToSearch = $this->createLocaleMock($localeString);
        $localeToFind = $this->createLocaleMock($localeString);

        /** @var Translation&MockObject */
        $translationToFind = $this->getMockBuilder(Translation::class)->getMock();
        $translationToFind->method('getLocale')->willReturn($localeToFind);

        /** @var Translation&MockObject */
        $translation = $this->getMockBuilder(Translation::class)->getMock();
        $translation->method('getLocale')->willReturn(null);

        /** @var Translatable */
        $model = $this->getMockForAbstractClass(Translatable::class);
        $model->addTranslation($translation);
        $model->addTranslation($translationToFind);

        $this->assertSame($translationToFind, $model->findTranslationByLocale($localeToSearch));
    }

    public function testFindTranslationByLocaleNothingFound(): void
    {
        $localeToSearch = $this->createLocaleMock('en-US');
        $localeToFind = $this->createLocaleMock('fr-FR');

        /** @var Translation&MockObject */
        $translationToFind = $this->getMockBuilder(Translation::class)->getMock();
        $translationToFind->method('getLocale')->willReturn($localeToFind);

        /** @var Translation&MockObject */
        $translation = $this->getMockBuilder(Translation::class)->getMock();
        $translation->method('getLocale')->willReturn(null);

        /** @var Translatable */
        $model = $this->getMockForAbstractClass(Translatable::class);
        $model->addTranslation($translation);
        $model->addTranslation($translationToFind);

        $this->assertNull($model->findTranslationByLocale($localeToSearch));
    }

    public function testLockCurrentTranslation(): void
    {
        /** @var Translation */
        $translation = $this->getMockBuilder(Translation::class)->getMock();

        /** @var Translation */
        $translation1 = $this->getMockBuilder(Translation::class)->getMock();

        /** @var Translatable */
        $model = $this->getMockForAbstractClass(Translatable::class);
        $model->addTranslation($translation1);

        $this->assertSame($model, $model->lockCurrentTranslation($translation));
        $this->assertSame([$translation], $model->getTranslations());
    }
}
