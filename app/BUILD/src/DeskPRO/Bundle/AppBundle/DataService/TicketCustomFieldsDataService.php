<?php

namespace DeskPRO\Bundle\AppBundle\DataService;

/**
 * Provides controller data access to the ticket labels.
 */
class TicketCustomFieldsDataService extends AbstractDataService
{
    /**
     * Get the list of available ticket labels.
     *
     * @return Doctrine\ORM\Query the list of all ticket flag names
     */
    public function getCustomFields()
    {
        return $this->em->getRepository('DeskPRO:CustomDefTicket')->findAll();
    }

    public function getCustomFieldsForTicket($ticket_id)
    {
        $ticket = $this->em->getRepository('DeskPRO:Ticket')->find($ticket_id);

        if (!$ticket) {
            return [];
        } else {
            return $ticket->custom_data;
        }
    }

    public function getSingleCustomFieldForTicket($ticket_id, $field_id)
    {
        /* So we're duplicating the labels per ticket. Hence we must do a group by operation
         * on the (hopefully) unique label names. That means using a querybuilder. */
        $repo = $this->em->getRepository('DeskPRO:CustomDataTicket');

        return $repo->findOneBy(['ticket' => $ticket_id, 'field' => $field_id]);
    }
}
