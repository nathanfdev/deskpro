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
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\DataService\Tickets\LegacyFilterSet;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Tickets\Filters;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class LegacyTicketFilterSetDataService.
 */
class LegacyTicketFilterSetDataService
{
    /**
     * @var TokenStorageInterface
     */
    private $token_storage;

    /**
     * Constructor.
     *
     * @param TokenStorageInterface $token_storage
     */
    public function __construct(TokenStorageInterface $token_storage)
    {
        $this->token_storage = $token_storage;
    }

    /**
     * @param int $id
     *
     * @return LegacyTicketFilterSet
     */
    public function getFilterSet($id)
    {
        $sets = $this->getFilterSetsData();

        return isset($sets[$id]) ? $sets[$id] : null;
    }

    /**
     * @return LegacyTicketFilterSet[]
     */
    public function getAllFilterSets()
    {
        return array_values($this->getFilterSetsData());
    }

    /**
     * @return array
     */
    private function getFilterSetsData()
    {
        $awaiting_agent_set = new LegacyTicketFilterSet(LegacyTicketFilterSet::TYPE_AWAITING_AGENT, 'Awaiting agent');
        $all_tickets_set    = new LegacyTicketFilterSet(LegacyTicketFilterSet::TYPE_ALL_TICKETS, 'All tickets');
        $custom_filters_set = new LegacyTicketFilterSet(LegacyTicketFilterSet::TYPE_CUSTOM_FILTERS, 'Custom filters');

        $filters     = new Filters();
        $all_filters = $filters->getFiltersForPerson($this->getUser());

        foreach ($all_filters as $filter) {
            if ($filter->sys_name) {
                $custom_filters_set->addFilter($filter);
            } elseif ($filter->terms) {
                $awaiting_agent_set->addFilter($filter);
            } else {
                $all_tickets_set->addFilter($filter);
            }
        }

        return [
            LegacyTicketFilterSet::TYPE_AWAITING_AGENT => $awaiting_agent_set,
            LegacyTicketFilterSet::TYPE_ALL_TICKETS    => $all_tickets_set,
            LegacyTicketFilterSet::TYPE_CUSTOM_FILTERS => $custom_filters_set,
        ];
    }

    /**
     * @return Person
     */
    private function getUser()
    {
        $user = $this->token_storage->getToken()->getUser();
        $user->loadHelper('AgentTeam');
        $user->loadHelper('AgentPermissions');

        return $user;
    }
}
