<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldRenderer;

use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\Form\FormField;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsContext;

/**
 * Interface FieldRendererInterface.
 */
interface FieldRendererInterface
{
    /**
     * @param TicketWithLayoutsContext $context
     * @param LayoutField              $field
     * @param FormField                $formField
     */
    public function addField(TicketWithLayoutsContext $context, LayoutField $field, FormField $formField);
}
