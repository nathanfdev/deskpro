<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Context\AgentContext;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;

class AgentPermsCollection
{
    /**
     * @var[]
     */
    private $agentContexts;

    /**
     * @param AgentContext[] $agentContexts
     */
    public function __construct(array $agentContexts)
    {
        $this->agentContexts = $agentContexts;
    }

    /**
     * @param TicketModel $ticketModel
     *
     * @return array
     */
    public function getPermsForTicket(TicketModel $ticketModel)
    {
        $canView  = [];
        $cantView = [];

        foreach ($this->agentContexts as $a) {
            if ($this->canView($ticketModel, $a)) {
                $canView[] = $a->id;
            } else {
                $cantView[] = $a->id;
            }
        }

        return [
            'canView'  => $canView,
            'cantView' => $cantView,
        ];
    }

    /**
     * @param TicketModel  $ticketModel
     * @param AgentContext $a
     *
     * @return bool
     */
    private function canView(TicketModel $ticketModel, AgentContext $a)
    {
        if ($a->view_all) {
            return true;
        }

        if ($ticketModel->agent === $a->agent_id) {
            return true;
        }

        if ($ticketModel->agent_team && in_array($ticketModel->agent_team, $a->teams)) {
            return true;
        }

        if (in_array($ticketModel->department, $a->allowed_departments)) {
            if ($a->view_assigned && $ticketModel->agent !== 0) {
                return true;
            }
            if ($a->view_unassigned && $ticketModel->agent === 0) {
                return true;
            }
        }

        return false;
    }
}
