<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\Mock;

use Marvin255\DoctrineTranslationBundle\Entity\Translatable;
use Marvin255\DoctrineTranslationBundle\Entity\Translation;

/**
 * @internal
 *
 * @extends Translation<Translatable>
 */
class MockNonTranslatableTranslation extends Translation
{
}
