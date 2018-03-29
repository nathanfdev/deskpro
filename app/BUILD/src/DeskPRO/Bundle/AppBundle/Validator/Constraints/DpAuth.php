<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class DpAuth.
 */
class DpAuth extends Constraint
{
    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'dp_auth_validator';
    }
}
