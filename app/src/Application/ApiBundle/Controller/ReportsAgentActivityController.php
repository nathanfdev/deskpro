<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;

use Orb\Util\Arrays;

class ReportsAgentActivityController extends AbstractController
{
    ####################################################################################################################
    # list
    ####################################################################################################################

    public function listAction($agent_or_team_id, $date)
    {
        /**
         * @var \Application\DeskPRO\Reports\AgentActivity $reports_agent_activity
         */
        $reports_agent_activity = $this->container->getSystemService('reports_agent_activity');
        $html_vars              = $reports_agent_activity->getVarsForHtmlView($agent_or_team_id, $date);
        $all_agents             = $reports_agent_activity->getAllAgents();
        $agent_teams            = $reports_agent_activity->getAllAgentTeams();

        return $this->createApiResponse(array(
             'all_agents'  => $this->getApiData(Arrays::flatten($all_agents)),
             'agent_teams' => $this->getApiData($agent_teams),
             'html'        => $this->renderView('ReportsInterfaceBundle:AgentActivity:results.html.twig', $html_vars),
        ));
    }
}
