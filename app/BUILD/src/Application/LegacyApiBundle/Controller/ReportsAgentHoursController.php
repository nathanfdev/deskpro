<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\LegacyApiBundle\PermissionStrategy\AgentPermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * @ApiModes("all")
 */
class ReportsAgentHoursController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        return new AgentPermission();
    }

    //###################################################################################################################
    // list
    //###################################################################################################################

    public function listAction($date1, $date2)
    {
        /** @var \Application\DeskPRO\Reports\AgentHours $reports_agent_hours */
        $reports_agent_hours = $this->container->getSystemService('reports_agent_hours');
        $html_vars           = $reports_agent_hours->getVarsForHtmlView($date1, $date2);

        return $this->createApiResponse([
            'html' => $this->renderView('ReportsInterfaceBundle:AgentHours:results.html.twig', $html_vars),
        ]);
    }
}
