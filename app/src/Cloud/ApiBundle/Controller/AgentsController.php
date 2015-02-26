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
 * @subpackage ApiBundle
 */

namespace Cloud\ApiBundle\Controller;

use Application\ApiBundle\Controller\AgentsController as BaseAgentsController;
use DeskPRO\Kernel\License;

class AgentsController extends BaseAgentsController
{
    /**
     * @param  int           $num
     * @return Response|null
     */
    protected function preNewAgent($num)
    {
        $current_agents = $this->db->fetchColumn("
            SELECT COUNT(*)
            FROM people
            WHERE is_agent = 1 AND is_deleted = 0
        ");

        $max_agents = License::getLicense()->getMaxAgents();

        $set = $current_agents + $num;
        if ($set > $max_agents) {
            $tmpdata = new \Application\DeskPRO\Entity\TmpData();
            $tmpdata->setType('dpc_set_plan');
            $tmpdata->setData('by_person', $this->person->getId());
            $tmpdata->setData('set_plan', $set);
            $tmpdata->date_expire = new \DateTime('+30 minutes');

            $this->em->persist($tmpdata);
            $this->em->flush();

            $url = DP_MA_SERVER . '/cloud/call/'.DPC_SITE_ID.'/'. $tmpdata->getCode();

            try {
                $client = new \Zend\Http\Client(null, array('timeout' => 15, 'sslverifypeer' => false));
                $client->setMethod(\Zend\Http\Request::METHOD_GET);
                $client->setUri($url);
                $client->send();
            } catch (\Exception $e) {
                throw $this->createNotFoundException();
            }
        }

        return null;
    }
}
