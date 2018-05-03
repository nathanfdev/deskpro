<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Ticket;

use Symfony\Component\Validator\Constraint;

/**
 * Class TicketLeafCategory.
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class TicketLeafCategory extends Constraint
{
    const NOT_ASSIGNABLE_CATEGORY = 'not_assignable_category';

    public $message = 'Unable to select parent category.';
}
