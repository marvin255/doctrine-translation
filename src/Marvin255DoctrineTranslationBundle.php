<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle;

use Marvin255\DoctrineTranslationBundle\Locale\LocaleType;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Bundle object.
 */
class Marvin255DoctrineTranslationBundle extends AbstractBundle
{
    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    /**
     * {@inheritdoc}
     */
    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $bundles = $builder->getParameter('kernel.bundles');

        if (!isset($bundles['DoctrineBundle'])) {
            return;
        }

        $builder->prependExtensionConfig(
            'doctrine',
            [
                'dbal' => [
                    'types' => [
                        LocaleType::LOCALE_TYPE => LocaleType::class,
                    ],
                ],
            ]
        );
    }
}
