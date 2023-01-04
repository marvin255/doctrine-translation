<?php

declare(strict_types=1);

namespace Marvin255\DoctrineTranslationBundle\Tests\Mock;

use Marvin255\DoctrineTranslationBundle\Entity\Translatable;
use Marvin255\DoctrineTranslationBundle\Entity\Translation;

/**
 * @internal
 *
 * @extends Translatable<Translation>
 */
class MockNoPairTranslatable extends Translatable
{
}
