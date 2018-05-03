<?php

/**
 * DeskPRO.
 *
 * @category Install
 */

namespace Application\InstallBundle\Data\DefaultData;

use Application\DeskPRO\Entity\TicketLayout;
use Application\DeskPRO\TicketLayout\Layout;
use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\Form\FormFields;

class TicketLayoutData extends AbstractDefaultData
{
    public function runInstall()
    {
        $exists = $this->getDb()->fetchColumn('SELECT id FROM ticket_layouts WHERE department_id IS NULL');

        if (!$exists) {
            $ticket_layout               = new TicketLayout();
            $ticket_layout->is_enabled   = true;
            $ticket_layout->user_layout  = new Layout();
            $ticket_layout->agent_layout = new Layout();

            foreach ([
                FormFields::PERSON,
                FormFields::DEPARTMENT,
                FormFields::SUBJECT,
                FormFields::MESSAGE,
                FormFields::ATTACHMENTS,
            ] as $field) {
                $ticket_layout->user_layout->add(new LayoutField($field));
                $ticket_layout->agent_layout->add(new LayoutField($field));
            }

            $ticket_layout->user_layout->add(new LayoutField(FormFields::ATTACHMENTS));

            $this->getEm()->persist($ticket_layout);
            $this->getEm()->flush();
        }
    }

    public function runReset()
    {
        $exists = $this->getDb()->fetchColumn('SELECT id FROM ticket_layouts WHERE department_id IS NULL');
        if ($exists) {
            $this->getDb()->delete('ticket_layouts', ['id' => $exists]);
        }
        $this->runInstall();
    }

    public function runSync()
    {
        $exists = $this->getDb()->fetchColumn('SELECT id FROM ticket_layouts WHERE department_id IS NULL');
        if (!$exists) {
            $this->runInstall();
        }
    }
}
