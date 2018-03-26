<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class DpPassword extends Constraint
{
    /** @var \Application\DeskPRO\Entity\Person */
    public $person = null;

    public function validatedBy()
    {
        return 'dp_password';
    }
}
