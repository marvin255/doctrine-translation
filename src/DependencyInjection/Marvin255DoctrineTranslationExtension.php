<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\DependencyInjection;

use Marvin255\DoctrineTranslationBundle\Locale\LocaleType;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Module's extension class.
 */
class Marvin255DoctrineTranslationExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container): void
    {
        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['DoctrineBundle'])) {
            $container->prependExtensionConfig(
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
}
