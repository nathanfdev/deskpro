<?php

namespace DeskPRO\Bundle\AppBundle\Validator\Constraints\Ticket;

use Symfony\Component\Validator\Constraint;

/**
 * Class TicketLeafProduct.
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class TicketLeafProduct extends Constraint
{
    const NOT_ASSIGNABLE_PRODUCT = 'not_assignable_product';

    public $message = 'Unable to select parent product.';
}
