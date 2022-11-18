<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\Locale;

use Marvin255\DoctrineTranslationBundle\Locale\Locale;
use Marvin255\DoctrineTranslationBundle\Locale\LocaleNormalizer;
use Marvin255\DoctrineTranslationBundle\Tests\BaseCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

/**
 * @internal
 */
class LocaleNormalizerTest extends BaseCase
{
    /**
     * @dataProvider provideNormalize
     */
    public function testNormalize(mixed $object, string|\Exception $reference): void
    {
        $normalizer = new LocaleNormalizer();

        if ($reference instanceof \Exception) {
            $this->expectExceptionObject($reference);
        }

        $result = $normalizer->normalize($object);

        if (!($reference instanceof \Exception)) {
            $this->assertSame($reference, $result);
        }
    }

    public function provideNormalize(): array
    {
        $mockLocale = 'en-US';

        return [
            'correct data' => [
                $this->createLocaleMock($mockLocale),
                $mockLocale,
            ],
            'null' => [
                null,
                new InvalidArgumentException('The object must implement the ' . Locale::class),
            ],
            'string' => [
                'string',
                new InvalidArgumentException('The object must implement the ' . Locale::class),
            ],
        ];
    }

    /**
     * @dataProvider provideSupportsNormalization
     */
    public function testSupportsNormalization(mixed $data, bool $reference): void
    {
        $normalizer = new LocaleNormalizer();
        $isSupport = $normalizer->supportsNormalization($data);

        $this->assertSame($reference, $isSupport);
    }

    public function provideSupportsNormalization(): array
    {
        return [
            'correct data' => [
                $this->createLocaleMock(),
                true,
            ],
            'null' => [
                null,
                false,
            ],
            'incorrect data' => [
                'en-US',
                false,
            ],
        ];
    }

    /**
     * @dataProvider provideDenormalize
     */
    public function testDenormalize(mixed $data, string $type, string|\Exception $reference): void
    {
        $normalizer = new LocaleNormalizer();

        if ($reference instanceof \Exception) {
            $this->expectExceptionObject($reference);
        }

        $result = $normalizer->denormalize($data, $type);

        if (!($reference instanceof \Exception)) {
            $this->assertInstanceOf(Locale::class, $result);
            $this->assertSame($reference, $result->getFull());
        }
    }

    public function provideDenormalize(): array
    {
        return [
            'correct data' => [
                'en-US',
                Locale::class,
                'en-US',
            ],
            'incorrect data' => [
                123123,
                Locale::class,
                new NotNormalizableValueException('The data is not a string'),
            ],
            'empty string' => [
                '',
                Locale::class,
                new NotNormalizableValueException("Locale can't be an empty string"),
            ],
        ];
    }

    /**
     * @dataProvider provideSupportsDenormalization
     */
    public function testSupportsDenormalization(mixed $data, string $type, bool $reference): void
    {
        $normalizer = new LocaleNormalizer();
        $isSupport = $normalizer->supportsDenormalization($data, $type);

        $this->assertSame($reference, $isSupport);
    }

    public function provideSupportsDenormalization(): array
    {
        return [
            'correct data' => [
                'en-US',
                Locale::class,
                true,
            ],
            'null' => [
                null,
                Locale::class,
                true,
            ],
            'incorrect data' => [
                'en-US',
                'test',
                false,
            ],
        ];
    }
}
