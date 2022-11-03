<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marvin255\DoctrineTranslationBundle\Entity\Translatable;

#[ORM\Entity]
class Test extends Translatable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
}