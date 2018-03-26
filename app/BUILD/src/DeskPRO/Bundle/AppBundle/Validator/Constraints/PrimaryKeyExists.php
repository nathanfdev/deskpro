<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class PrimaryKeyExists.
 */
class PrimaryKeyExists extends Constraint
{
    const RESOURCE_NOT_FOUND = 'resource_not_found';

    /**
     * The table name.
     *
     * @var string
     */
    public $table;

    /**
     * These values are excluded from validation.
     *
     * @var array
     */
    public $excluded_values;

    /**
     * @var string
     */
    public $message = 'The value was not found.';

    /**
     * {@inheritdoc}
     */
    public function getDefaultOption()
    {
        return 'table';
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'primary_key_exists_validator';
    }
}
