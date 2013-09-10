<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
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

class AgentsController extends AbstractController
{
	public function listAction()
	{
		$data = array('agents' => array());

		foreach ($this->container->getAgentData()->getAgents() as $agent) {
			$agent_data = array();

			foreach (array('first_name', 'last_name', 'name', 'override_display_name', 'can_admin', 'can_billing', 'can_reports', 'timezone') as $k) {
				$agent_data[$k] = $agent[$k];
			}

			$agent_data['primary_email'] = array(
				'id'    => $agent->primary_email->id,
				'email' => $agent->primary_email->email
			);

			$agent_data['emails'] = array();
			foreach ($agent->emails as $eml) {
				$agent_data['emails'][] = array('id' => $eml->id, 'email' => $eml->email);
			}

			$agent_data['usergroup_ids']  = array();
			$agent_data['agentgroup_ids'] = array();
			foreach ($agent->getUsergroupIds() as $ug_id) {
				if ($this->container->getDataService('Usergroup')->get($ug_id)->is_agent_group) {
					$agent_data['agentgroup_ids'][] = $ug_id;
				} else {
					$agent_data['usergroup_ids'][] = $ug_id;
				}
			}

			$data['agents'][] = $agent_data;
		}

		return $this->createApiResponse($data);
	}
}