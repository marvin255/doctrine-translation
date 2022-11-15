<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Locale;

use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Serializer object that is using to serialize and userialize locale objects.
 */
class LocaleNormalizer implements DenormalizerInterface, NormalizerInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException
     */
    public function normalize(mixed $object, string $format = null, array $context = [])
    {
        if (!($object instanceof Locale)) {
            throw new InvalidArgumentException('The object must implement the ' . Locale::class);
        }

        return $object->getFull();
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization(mixed $data, string $format = null): bool
    {
        return $data instanceof Locale;
    }

    /**
     * {@inheritDoc}
     *
     * @throws NotNormalizableValueException
     */
    public function denormalize(mixed $data, string $type, string $format = null, array $context = [])
    {
        if (!\is_string($data)) {
            throw new NotNormalizableValueException('The data is not a string');
        }

        try {
            $locale = LocaleFactory::create($data);
        } catch (\Throwable $e) {
            throw new NotNormalizableValueException($e->getMessage());
        }

        return $locale;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsDenormalization(mixed $data, string $type, string $format = null): bool
    {
        return Locale::class === $type;
    }
}
