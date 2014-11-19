<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Doctrine\Common\Util\ClassUtils;

class SetTicketContextualField extends AbstractSetCustomField
{
    /**
     * @param  Ticket                                         $ticket
     * @param  ExecutorContextInterface                       $context
     * @return \Application\DeskPRO\CustomFields\FieldManager
     */
    public function getFieldManager(Ticket $ticket, ExecutorContextInterface $context)
    {
        return $this->getContainer()->getTicketFieldManager();
    }


    /**
     * @param  Ticket                   $ticket
     * @param  ExecutorContextInterface $context
     * @return mixed
     */
    public function getApplicableObject(Ticket $ticket, ExecutorContextInterface $context)
    {
        return $ticket;
    }

    /**
     * {@inheritDoc}
     */
    public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
    {
        $fm = $this->getContainer()->getCustomFieldManager();
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

        $form = $fm->createFieldForm($def, $ticket, $formContext, array('allow_edit' => true));
        $form->submit(array('custom_choice' => $value));
        if ($form->isValid()) {
            $fm->flush($form);
        }
    }
}
