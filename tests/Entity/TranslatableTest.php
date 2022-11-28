<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\Entity;

use Marvin255\DoctrineTranslationBundle\Entity\Translatable;
use Marvin255\DoctrineTranslationBundle\Entity\Translation;
use Marvin255\DoctrineTranslationBundle\Tests\BaseCase;

/**
 * @internal
 */
class TranslatableTest extends BaseCase
{
    public function testAddTranslation(): void
    {
        /** @var Translatable */
        $model = $this->getMockForAbstractClass(Translatable::class);

        $translation = $this->createTranslationMock();
        $translation->expects($this->once())
            ->method('setTranslatable')
            ->with($this->identicalTo($model))
            ->willReturnSelf();

        $this->assertSame($model, $model->addTranslation($translation));
        $this->assertSame([$translation], $model->getTranslations());
    }

    public function testClearTranslations(): void
    {
        $translation = $this->createTranslationMock();
        $translation1 = $this->createTranslationMock();

        /** @var Translatable */
        $model = $this->getMockForAbstractClass(Translatable::class);
        $model->addTranslation($translation);
        $model->addTranslation($translation1);

        $this->assertSame($model, $model->clearTranslations());
        $this->assertSame([], $model->getTranslations());
    }

    public function testRemoveTranslation(): void
    {
        /** @var Translatable */
        $model = $this->getMockForAbstractClass(Translatable::class);

        $translation = $this->createTranslationMock();
        $translation->expects($this->exactly(2))
            ->method('setTranslatable')
            ->withConsecutive(
                [$this->identicalTo($model)],
                [$this->equalTo(null)]
            );

        $this->assertSame($model, $model->addTranslation($translation));
        $this->assertSame($model, $model->removeTranslation($translation));
        $this->assertSame([], $model->getTranslations());
    }

    public function testFindTranslationByLocale(): void
    {
        $localeString = 'en-US';
        $localeToSearch = $this->createLocaleMock($localeString);
        $localeToFind = $this->createLocaleMock($localeString);

        $translationToFind = $this->createTranslationMock(null, $localeToFind);
        $translation = $this->createTranslationMock();

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

        $translationToFind = $this->createTranslationMock(null, $localeToFind);
        $translation = $this->createTranslationMock();

        /** @var Translatable */
        $model = $this->getMockForAbstractClass(Translatable::class);
        $model->addTranslation($translation);
        $model->addTranslation($translationToFind);

        $this->assertNull($model->findTranslationByLocale($localeToSearch));
    }

    /**
     * @psalm-param Translation|null $set
     * @psalm-param Translation|null $reference
     *
     * @dataProvider provideSetGetTranslated
     */
    public function testSetGetTranslated(?Translation $set, ?Translation $reference): void
    {
        /** @var Translatable */
        $model = $this->getMockForAbstractClass(Translatable::class);

        $this->assertSame($model, $model->setTranslated($set));
        $this->assertSame($reference, $model->getTranslated());
    }

    public function provideSetGetTranslated(): array
    {
        $translation = $this->createTranslationMock();

        return [
            'single translation' => [
                $translation,
                $translation,
            ],
            'null' => [
                null,
                null,
            ],
        ];
    }
}
