<?php

namespace Application\DeskPRO\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

class Tickets
{
    /**
     * Get an array of tickets from the passed IDs.
     *
     * @param array $ids
     *
     * @return array
     */
    public function getTicketsFromIds(array $ids)
    {
        return App::getOrm()->getRepository('DeskPRO:Ticket')->getTicketsFromIds($ids);
    }

    /**
     * @param Entity\Ticket $ticket
     *
     * @return TicketEdit
     */
    public function getTicketEditor(Entity\Ticket $ticket)
    {
        return new TicketEdit($ticket);
    }

    /**
     * Get an array of various options used on the new ticket page.
     *
     * @param mixed $person The person we're fetching for. This will define the permissions/context
     *
     * @return array
     */
    public function getTicketOptions($person)
    {
        $options = [];

        if ($person['is_agent']) {
            $options['agents'] = App::$container->get('agent_data')->getAgentNames();

            if (App::getSetting('core.use_agent_team')) {
                $options['agent_teams'] = App::getDataService('AgentTeam')->getTeamNames();
            } else {
                $options['agent_teams'] = [];
            }
        }

        $options['departments'] = App::getDataService('Department')->getNames(null, false);

        if (App::getSetting('core.use_ticket_category')) {
            $options['ticket_categories_hierarchy'] = App::getDataService('TicketCategory')->getInHierarchy();
            $options['ticket_categories_full']      = App::getDataService('TicketCategory')->getFullNames(null, false);
            $options['ticket_categories']           = App::getDataService('TicketCategory')->getNames(null, false);
        } else {
            $options['ticket_categories_hierarchy'] = [];
            $options['ticket_categories_full']      = [];
            $options['ticket_categories']           = [];
        }

        if (App::getSetting('core.use_ticket_workflow')) {
            $options['ticket_workflows'] = App::getDataService('TicketWorkflow')->getNames();
        } else {
            $options['ticket_workflows'] = [];
        }

        if (App::getSetting('core.use_product')) {
            $options['products']           = App::getDataService('Product')->getNames();
            $options['products_hierarchy'] = App::getDataService('Product')->getInHierarchy();
        } else {
            $options['products']           = [];
            $options['products_hierarchy'] = [];
        }

        $options['slas'] = App::getDataService('Sla')->getSlaTitles();

        if (App::getSetting('core.use_ticket_priority')) {
            $options['priorities'] = App::getDataService('TicketPriority')->getNames();
        } else {
            $options['priorities'] = [];
        }
        $options['ticket_priorities'] = $options['priorities'];

        return $options;
    }
}
