<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Bundle object.
 */
class Marvin255DoctrineTranslationBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
