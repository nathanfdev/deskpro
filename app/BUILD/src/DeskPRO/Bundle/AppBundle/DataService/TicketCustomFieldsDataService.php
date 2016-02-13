<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 */
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
