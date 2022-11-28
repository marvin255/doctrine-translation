<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\StateHandler;

use Marvin255\DoctrineTranslationBundle\Entity\Translation;
use Marvin255\DoctrineTranslationBundle\StateHandler\TranslatableStateHandler;
use Marvin255\DoctrineTranslationBundle\Tests\EmCase;

/**
 * @internal
 */
class TranslatableStateHandlerTest extends EmCase
{
    public function testReplaceTranslationsWrongTranslationObject(): void
    {
        $translatable = $this->createTranslatableMock();
        $translation = $this->createTranslationMock();
        $em = $this->createEmMock();
        $classNameManager = $this->createClassNameManagerMock();

        $handler = new TranslatableStateHandler($em, $classNameManager);

        $this->expectException(\InvalidArgumentException::class);
        $handler->replaceTranslations($translatable, [$translation]);
    }

    public function testReplaceTranslationsAddNewTranslation(): void
    {
        $locale = $this->createLocaleMock();

        $translation = $this->createTranslationMock(null, $locale);

        $translatable = $this->createTranslatableMock();
        $translatable->expects($this->once())
            ->method('findTranslationByLocale')
            ->with($this->identicalTo($locale))
            ->willReturn(null);
        $translatable->expects($this->once())
            ->method('addTranslation')
            ->with($this->identicalTo($translation))
            ->willReturnSelf();

        $em = $this->createEmMock();
        $em->expects($this->once())
            ->method('persist')
            ->with($this->identicalTo($translation));
        $em->expects($this->never())->method('remove');
        $em->expects($this->once())->method('flush');

        $classNameManager = $this->createBasicClassNameManagerMock($translatable, $translation);

        $handler = new TranslatableStateHandler($em, $classNameManager);

        $handler->replaceTranslations($translatable, [$translation]);
    }

    public function testReplaceTranslationsDeleteTranslation(): void
    {
        $translation = $this->createTranslationMock();

        $translatable = $this->createTranslatableMock();
        $translatable->expects($this->once())
            ->method('getTranslations')
            ->willReturn([$translation]);
        $translatable->expects($this->once())
            ->method('removeTranslation')
            ->with($this->identicalTo($translation))
            ->willReturnSelf();

        $em = $this->createEmMock();
        $em->expects($this->once())
            ->method('remove')
            ->with($this->identicalTo($translation));
        $em->expects($this->never())->method('persist');
        $em->expects($this->once())->method('flush');

        $classNameManager = $this->createBasicClassNameManagerMock();

        $handler = new TranslatableStateHandler($em, $classNameManager);

        $handler->replaceTranslations($translatable, []);
    }

    public function testReplaceTranslationsUpdateTranslation(): void
    {
        $locale = $this->createLocaleMock();
        $existedTranslation = $this->createTranslationMock(null, $locale);
        $translation = $this->createTranslationMock(null, $locale);

        $translatable = $this->createTranslatableMock();
        $translatable->expects($this->once())
            ->method('findTranslationByLocale')
            ->with($this->identicalTo($locale))
            ->willReturn($existedTranslation);

        $meta = $this->createMetaMock();
        $meta->expects($this->once())
            ->method('getFieldNames')
            ->willReturn([Translation::ID_COLUMN_NAME, 'test']);
        $meta->expects($this->once())
            ->method('getFieldValue')
            ->with(
                $this->identicalTo($translation),
                $this->identicalTo('test')
            )
            ->willReturn('value');
        $meta->expects($this->once())
            ->method('setFieldValue')
            ->with(
                $this->identicalTo($existedTranslation),
                $this->identicalTo('test'),
                $this->identicalTo('value')
            );

        $em = $this->createEmMock([\get_class($translation) => $meta]);
        $em->expects($this->never())->method('remove');
        $em->expects($this->never())->method('persist');
        $em->expects($this->once())->method('flush');

        $classNameManager = $this->createBasicClassNameManagerMock($translatable, $translation);

        $handler = new TranslatableStateHandler($em, $classNameManager);

        $handler->replaceTranslations($translatable, [$translation]);
    }
}
