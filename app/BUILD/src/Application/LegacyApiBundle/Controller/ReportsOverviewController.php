<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * @ApiModes("all")
 */
class ReportsOverviewController extends AbstractController
{
    //###################################################################################################################
    // get data (for specified type)
    //###################################################################################################################

    public function getDataAction($type)
    {
        /*
         * @var \Application\DeskPRO\Reports\Overview
         */
        $reports_overview = $this->container->getSystemService('reports_overview');
        $reports_overview->setPerson($this->person);

        $options = [];

        $agentTeam = $this->in->getCleanValue('agent_team', 'integer');
        if ($agentTeam) {
            $options['agent_team'] = $agentTeam;
        }

        return $this->createApiResponse($reports_overview->getOverviewData($type, $options));
    }

    //###################################################################################################################
    // get statistics (for specified type)
    //###################################################################################################################

    public function getStatsAction($type)
    {
        /*
         * @var \Application\DeskPRO\Reports\Overview
         */
        $reports_overview = $this->container->getSystemService('reports_overview');
        $reports_overview->setPerson($this->person);

        $grouping_field = $this->in->getString('grouping_field');
        $options        = [
            'date_choice' => $this->in->getString('date_choice'),
            'sla_id'      => $this->in->getString('sla_id'),
        ];

        try {
            return $this->createApiResponse($reports_overview->getStats($type, $grouping_field, $options));
        } catch (\InvalidArgumentException $e) {
            return $this->createApiResponse($reports_overview->getStats($type, 'department'));
        }
    }
}
