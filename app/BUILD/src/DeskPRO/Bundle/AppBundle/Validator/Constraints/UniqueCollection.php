<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class UniqueCollection.
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class UniqueCollection extends Constraint
{
    const NOT_UNIQUE = 'not_unique_collection';

    public $message = 'One or more of the given values is not unique.';

    /**
     * Check object properties.
     * Could be string or array of strings.
     *
     * @var array|string
     */
    public $property = null;
}
