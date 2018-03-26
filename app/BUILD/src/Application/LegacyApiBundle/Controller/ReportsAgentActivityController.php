<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Orb\Util\Arrays;

/**
 * @ApiModes("all")
 */
class ReportsAgentActivityController extends AbstractController
{
    //###################################################################################################################
    // list
    //###################################################################################################################

    public function listAction($agent_or_team_id, $date)
    {
        /** @var \Application\DeskPRO\Reports\AgentActivity $reports_agent_activity */
        $reports_agent_activity = $this->container->getSystemService('reports_agent_activity');
        $html_vars              = $reports_agent_activity->getVarsForHtmlView($agent_or_team_id, $date);
        $all_agents             = $reports_agent_activity->getAllAgents();
        $agent_teams            = $reports_agent_activity->getAllAgentTeams();

        return $this->createApiResponse([
             'all_agents'  => $this->getApiData(Arrays::flatten($all_agents)),
             'agent_teams' => $this->getApiData($agent_teams),
             'html'        => $this->renderView('ReportsInterfaceBundle:AgentActivity:results.html.twig', $html_vars),
        ]);
    }
}
