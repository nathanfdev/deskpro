<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class ObjectAlias.
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class ObjectAlias extends Constraint
{
    const INVALID_ALIAS_FORMAT = 'invalid_alias_format';
    const NOT_UNIQUE_ALIAS     = 'not_unique_alias';

    public $invalidFormatMessage = 'Format of the alias is not valid.';
    public $notUniqueMessage     = 'This alias {{alias}} already exists.';

    /**
     * @var mixed
     */
    public $owner;
}
