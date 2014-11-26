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
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Tickets\Filters\FilterChangeDetector;
use Orb\Util\Arrays;

class TicketFilterChangeDetectorService
{
    public static function create(DeskproContainer $container)
    {
        $agent_data = $container->getAgentData();
        $agents     = $agent_data->getAgents();

        foreach ($agents as $a) {
            $a->loadHelper('AgentPermissions');
            $a->loadHelper('PermissionsManager');
            $a->loadHelper('Agent');
        }

        $filters = $container->getEm()->getRepository('DeskPRO:TicketFilter')->getFilters();

        $x = new FilterChangeDetector(
            $container->getEm()->getRepository('DeskPRO:TicketFilter')->getFilters(),
            $container->getAgentData()->getAgents()
        );

        $change_subs = $container->getEm()->getRepository('DeskPRO:TicketFilterSubscription')->getSimplePropertyChangeSubscriptions();

        if ($change_subs) {
            $filters = Arrays::keyFromData($filters, 'id');
            foreach ($change_subs as $sub) {
                if (!isset($filters[$sub['filter_id']]) || !$agent_data->has($sub['person_id'])) {
                    continue;
                }

                $x->addExplicitFilterScope(
                    $filters[$sub['filter_id']],
                    $agent_data->get($sub['person_id'])
                );
            }
        }

        return $x;
    }
}
