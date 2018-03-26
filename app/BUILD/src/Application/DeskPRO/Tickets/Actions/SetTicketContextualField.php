<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Doctrine\Common\Util\ClassUtils;

class SetTicketContextualField extends AbstractSetCustomField
{
    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     *
     * @return \Application\DeskPRO\CustomFields\FieldManager
     */
    public function getFieldManager(Ticket $ticket, ExecutorContextInterface $context)
    {
        return $this->getContainer()->getTicketFieldManager();
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     *
     * @return mixed
     */
    public function getApplicableObject(Ticket $ticket, ExecutorContextInterface $context)
    {
        return $ticket;
    }

    /**
     * {@inheritdoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $fm       = $this->getContainer()->getCustomFieldManager();
        $field_id = $this->getActionOption('field_id');
        $value    = $this->getActionOption('value');

        if (!$def = $fm->getDefinition($field_id)) {
            return;
        }

        $formContext = null;
        if ($def['context_class']) {
            // todo
            $formContext = $ticket->person;
            if ($def['context_class'] !== ClassUtils::getClass($formContext)) {
                $formContext = $formContext->organization;
            }

            if (!$formContext) {
                return;
            }
        }

        $form = $fm->createFieldForm($def, $ticket, $formContext, ['allow_edit' => true]);
        $form->submit(['custom_choice' => $value]);
        if ($form->isValid()) {
            $fm->flush($form);
        }
    }
}
