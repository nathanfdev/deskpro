<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

/**
 * Class Url.
 */
class Url extends \Symfony\Component\Validator\Constraints\Url
{
    /**
     * @var bool
     */
    public $allowFile = false;
}
