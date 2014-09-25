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

use Application\ApiBundle\PermissionStrategy\AdminManagePermission;
use Application\ApiBundle\PermissionStrategy\MultiPermissions;
use Application\ApiBundle\PermissionStrategy\PassPermission;
use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Person;
use Orb\Util\Arrays;

class AgentTeamsController extends AbstractController implements ProtectedControllerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getPermissionStrategy()
	{
		$multi = new MultiPermissions();
		$multi->addPermissionStrategy(new AdminManagePermission());
		$multi->addPermissionStrategy(new PassPermission(), 'listTeamsAction');
		return $multi;
	}


	####################################################################################################################
	# list-teams
	####################################################################################################################

	public function listTeamsAction()
	{
		$data = array('agent_teams' => array());

		foreach ($this->container->getDataService('AgentTeam')->getTeams() as $agent_team) {
			$data['agent_teams'][] = $agent_team->toApiData();
		}

		return $this->createApiResponse($data);
	}


	####################################################################################################################
	# get-team
	####################################################################################################################

	public function getTeamAction($id)
	{
		$team = $this->getContainer()->getAgentData()->getTeam($id);

		if (!$team) {
			throw $this->createNotFoundException();
		}

		$data = $team->toApiData();
		$data['members'] = array();

		foreach ($team->members as $agent) {
			$data['members'][] = $agent->toBasicApiData();
		}

		return $this->createApiResponse(array('team' => $data));
	}


	####################################################################################################################
	# delete-team
	####################################################################################################################

	public function deleteTeamAction($id)
	{
		$team = $this->getContainer()->getAgentData()->getTeam($id);

		if (!$team) {
			throw $this->createNotFoundException();
		}

		$old_id = $team->id;
		$this->em->remove($team);
		$this->em->flush();

		return $this->createApiDeleteResponse(array(
			'old_team_id' => $old_id
		));
	}


	####################################################################################################################
	# save-team
	####################################################################################################################

	public function saveTeamAction($id)
	{
		if ($id) {
			if (!$team = $this->em->find('DeskPRO:AgentTeam', $id)) {
				throw $this->createNotFoundException();
			}
		} else {
			$team = new AgentTeam();
			$this->em->persist($team);
		}

		$team->name = $this->in->getString('team.name');

		// save avatar
		if ($blobId = $this->in->getUint('team.avatar')) {
			if ($team->avatar && $blobId != $team->avatar['id']) {
				$this->em->remove($team->avatar);
			}
			$team->avatar = $this->em->getReference('DeskPRO:Blob', $blobId);
		} elseif($team->avatar) {
			$team->avatar && $this->em->remove($team->avatar);
			$team->avatar = null;
		}

		$errors = $this->container->getValidator()->validate($team);
		if (count($errors)) {
			return $this->createApiValidationErrorResponse($errors);
		}

		// todo handle image

		#------------------------------
		# Save members
		#------------------------------

		$new_members = $this->in->getArrayOfUInts('team.person_ids');
		$new_members = array_unique($new_members);
		$new_members = Arrays::removeFalsey($new_members);

		if ($new_members) {
			$agent_data = $this->container->getAgentData();
			$new_members = array_filter($new_members, function($a) use ($agent_data) {
				return $agent_data->get($a) ? true : false;
			});
		}

		$members = $this->em->getRepository('DeskPRO:Person')->findBy(array('id' => $new_members));
		$team->members->clear();
		foreach ($members as $person) {
			/** @var $person Person */
			$person->addTeam($team); // bidirectional
		}

		$is_new = (bool) $team['id'];
		$this->em->flush();

		if ($is_new) {
			return $this->createApiCreateResponse(
				array('team_id' => $team->id),
				$this->generateUrl('api_agent_teams_get', array('id' => $team->id), true)
			);
		} else {
			return $this->createApiSuccessResponse(array('team_id' => $team->id));
		}
	}
}