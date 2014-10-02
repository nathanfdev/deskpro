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

namespace Application\ApiBundle\Controller;

use Application\ApiBundle\HttpFoundation\JsonResponse;
use Application\ApiBundle\PermissionStrategy\AdminManagePermission;
use Application\DeskPRO\DependencyInjection\SystemServices\PersonApiDataFactoryService;
use Application\DeskPRO\Entity\PasswordHistory;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\People\AgentNotifPrefs\Prefs as AgentNotifPrefs;
use Application\DeskPRO\People\AgentNotifPrefs\Prefs;
use Application\DeskPRO\People\AgentNotifPrefs\PrefsLoader as AgentNotifPrefsLoader;
use Application\DeskPRO\People\AgentNotifPrefs\PrefsPersister;
use Application\DeskPRO\People\AgentNotifPrefs\PrefsTable as AgentNotifPrefsTable;
use Application\DeskPRO\People\AgentPermissions\AgentPermissions;
use Application\DeskPRO\People\AgentPermissions\GroupDbPersister;
use Application\DeskPRO\People\AgentPermissions\PersonDbLoader as AgentPermsPersonDbLoader;
use Application\DeskPRO\People\Agents\AgentDelete;
use Application\DeskPRO\People\Agents\EditAgent;
use Application\DeskPRO\People\Agents\Type\EditAgentType;
use DeskPRO\Kernel\License;
use Orb\Util\Arrays;
use Orb\Util\PhoneNumbers;
use Orb\Util\Strings;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AgentsController extends AbstractController implements ProtectedControllerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getPermissionStrategy()
	{
		return new AdminManagePermission();
	}

	####################################################################################################################
	# list-agents
	####################################################################################################################

	public function listAgentsAction()
	{
		$data = array('agents' => array());

		$online_agents_userchat = $this->em->getRepository('DeskPRO:Person')->getActiveAgentIdsForUserChat();
		$online_agents_userchat = array_fill_keys($online_agents_userchat, true);

		$mode = 'normal';

		if ($this->in->getBool('full')) {
			$mode = 'full';
		} else if ($this->in->getBool('basic')) {
			$mode = 'basic';
		}

		foreach ($this->container->getAgentData()->getAgents() as $agent) {
			switch ($mode) {
				case 'full':
					$agent_data = $this->getFullAgentData($agent['id']);
					break;

				case 'basic':
					$agent_data = $agent->toBasicApiData();
					break;

				default:
					$agent_data = $agent->toApiData();
					$agent_data['is_online_now'] = $this->container->getAgentData()->isAgentOnline($agent);
					$agent_data['is_available_chat'] = isset($online_agents_userchat[$agent->id]);
			}

			if ($this->in->getBool('with_perms')) {
				$perm_loader = new AgentPermsPersonDbLoader($agent, $this->em);
				$agent_data['perms'] = $perm_loader->getEffectivePermissions()->toArray();
			}

			$data['agents'][] = $agent_data;
		}

		return $this->createApiResponse($data);
	}


	####################################################################################################################
	# list-deleted-agents
	####################################################################################################################

	public function listDeletedAgentsAction()
	{
		$deleted_agents = $this->em->getRepository('DeskPRO:Person')->getDeletedAgents();

		$data = array('agents' => array());

		foreach ($deleted_agents as $agent) {
			$data['agents'][] = $agent->toApiData();
		}

		return $this->createApiResponse($data);
	}


	####################################################################################################################
	# get-agent
	####################################################################################################################

	protected function getFullAgentData($id)
	{
		$agent = $this->container->getAgentData()->get($id);

		if (!$agent) {
			throw $this->createNotFoundException();
		}

		$serializer = $this->getContainer()->getSystemService('serializer');
		$agent_data = $serializer->serialize($agent);

		$agent_data['teams'] = array();

		$agent->loadHelper('Agent');
		$agent->loadHelper('AgentTeam');
		$agent->loadHelper('AgentPermissions');
		$agent->loadHelper('PermissionsManager');

		foreach ($this->container->getAgentData()->getTeamsByIds($agent->getHelper('AgentTeam')->getAgentTeamIds()) as $t) {
			$agent_data['teams'][] = $t->toApiData();
		}

		$perm_loader = new AgentPermsPersonDbLoader($agent, $this->em);

		$data = array(
			'agent' => $agent_data,
			'perms' => $perm_loader->getEffectivePermissions()->toArray(),
		);

		if ($this->in->getBool('extended')) {
			$data['signature_html'] = $agent->getSignatureHtml();
		}

		$data['person_id'] = $agent['id']; // back compatibility
		return $data;
	}

	public function getAgentAction($id)
	{
		$data = $this->getFullAgentData($id);

		return $this->createApiResponse($data);
	}

	####################################################################################################################
	# get-deleted-agent
	####################################################################################################################

	public function getDeletedAgentAction($id)
	{
		$agent = $this->em->find('DeskPRO:Person', $id);

		if (!$agent || !$agent->is_agent || !$agent->is_deleted) {
			throw $this->createNotFoundException();
		}

		$agent_data = $agent->toApiData();
		$agent_data['teams'] = array();

		$agent->loadHelper('Agent');
		$agent->loadHelper('AgentTeam');
		$agent->loadHelper('AgentPermissions');
		$agent->loadHelper('PermissionsManager');

		foreach ($this->container->getAgentData()->getTeamsByIds($agent->getHelper('AgentTeam')->getAgentTeamIds()) as $t) {
			$agent_data['teams'][] = $t->toApiData();
		}

		$perm_loader = new AgentPermsPersonDbLoader($agent, $this->em);

		return $this->createApiResponse(array(
			'agent'           => $agent_data,
			'perms'           => $perm_loader->getEffectivePermissions()->toArray(),
		));
	}


	####################################################################################################################
	# save-agent
	####################################################################################################################

	public function saveAgentAction($id = null)
	{
		$agent_postdata = $this->in->getArrayValue('agent');

		$filter_subs = $this->in->getArrayValue('filter_subs');
		$other_subs = $this->in->getArrayValue('other_subs');
		$quick_add = $this->in->getBool('quick_add');
		$perm_overrides = $this->in->getArrayValue('perm_overrides');
		$dep_perm_overrides = $this->in->getArrayValue('dep_perm_overrides');
		$profile = $this->in->getArrayValue('profile');
		$skip_email = $this->in->getBool('skip_email');

		return $this->saveAgent($id, $agent_postdata, $profile, $filter_subs, $other_subs, $quick_add, $perm_overrides, $dep_perm_overrides, $skip_email);
	}

	protected function saveAgent($id = null, $agent_postdata = array(), $profile = array(), $filter_subs = array(),
		$other_subs = array(), $quick_add = false, $perm_overrides = array(),
		$dep_perm_overrides = array(), $skip_email = false)
	{
		#-------------------------
		# Pre-validation
		#-------------------------

		$exist_person = null;

		if (!isset($agent_postdata['emails'])) {
			$agent_postdata['emails'] = array();
		}
		$set_emails = $agent_postdata['emails'];
		if (isset($agent_postdata['email'])) {
			array_unshift($set_emails, $agent_postdata['email']);
		}

		$set_emails = array_unique($set_emails);
		$set_emails = Arrays::removeFalsey($set_emails);

		$email_account_manager = $this->container->getEmailAccountManager();
		$system_addresses = array_filter($set_emails, function($e) use ($email_account_manager) {
			return $email_account_manager->findAccountForEmailAddress($e);
		});

		if ($system_addresses) {
			return $this->createApiErrorInfoResponse(
				'system_email_addresses',
				'One or more email addresses you entered are already being used as email accounts.',
				array('emails' => array_values($system_addresses))
			);
		}

		if (isset($agent_postdata['primary_phone_number_text'])) {
			$phone_number = $agent_postdata['primary_phone_number_text'];
			if (!PhoneNumbers::looksEmpty($phone_number)) {
				if (!PhoneNumbers::isValid($phone_number)) {
					return $this->createApiErrorInfoResponse('invalid_phone_number',
						'Invalid phone number format.',
						array( 'primary_phone_number_text' => $phone_number ));
				}
			}
		}

		$email_validator = $this->container->getSystemService('email_address_validator');
		$set_emails = array_filter($set_emails, function($e) use ($email_validator) {
			return $email_validator->isValidUserEmail($e);
		});

		$existPersons = $this->em->getRepository('DeskPRO:Person')->findByEmails($set_emails);

		// we have a dupe email error
		if(count($existPersons) > 1) {
			$error_info = array('existing' => array());

			foreach ($existPersons as $person) {
				$error_info['existing'][] = array(
					'person_id'   => $person['id'],
					'person_name' => $person['display_name'],
					'email'       => implode(', ', $person->getEmailAddresses()),
				);
			}
			return $this->createApiErrorInfoResponse('dupe_email', 'One or more email addresses are already in use by other users', $error_info);

		}

		#-------------------------
		# Get agent
		#-------------------------

		if (!$agent = reset($existPersons)) {
			if ($id) {
				if (!$agent = $this->container->getAgentData()->get($id)) {
					throw $this->createNotFoundException();
				}
			} else {
				$agent = new Person();
			}
		}

		// Promoting an existing user to an agent needs to call the preNewAgent callback
		if (!$agent['is_agent']) {
			$r = $this->preNewAgent(1);
			if ($r) return $r;
		}

		// If the record isnt a user yet, then we need to set an initial password
		if (!$agent->is_user) {
			$agent->setPassword(Strings::randomPronounceable(20, 4));
		}

		$edit_agent = new EditAgent($agent);

		#-------------------------
		# Save form
		#-------------------------

		$form = $this->createForm(
			new EditAgentType(),
			$edit_agent
		);

		// We did a bit of pre-cleanup above to prepend
		// primary address to emails list
		unset($agent_postdata['email']);
		$agent_postdata['emails'] = $set_emails;

		$form->submit($agent_postdata, false);

		if (!$form->isValid()) {
			return $this->createApiFormErrorResponse($form);
		}

		$edit_agent->save($this->em);

		#-------------------------
		# Save subscriptions
		#-------------------------

		if ($filter_subs && $other_subs) {
			$notif_pref_loader = new AgentNotifPrefsLoader($agent, $this->em);
			$notif_prefs = $notif_pref_loader->getPrefsFromArray(
				$filter_subs,
				$other_subs
			);

			$notif_perist = new PrefsPersister($agent, $this->em);
			$notif_perist->savePrefs($notif_prefs);
		}

		#-------------------------
		# Quick add: add 'all perms' group
		#-------------------------

		if ($quick_add) {
			$ug = $this->container->getAgentGroups()->getSysGroup('agent_all_perms');
			$agent->usergroups->add($ug);
			$this->em->persist($agent);
			$this->em->flush();
		}

		#-------------------------
		# Save permission overrides
		#-------------------------

		if ($perm_overrides) {
			$perms = new AgentPermissions();
			$perms->fromArray($perm_overrides);

			$persister = new GroupDbPersister($this->em);
			$persister->saveOverridePerms($agent, $perms);
		}

		#-------------------------
		# Save department permission overrides
		#-------------------------

		if ($dep_perm_overrides) {
			$ticket_deps = $this->container->getTicketDepartments();
			$chat_deps   = $this->container->getChatDepartments();

			$set_perms = array();
			foreach ($dep_perm_overrides['tickets'] as $did => $p) {
				if (!($dep = $ticket_deps->getById($did))) continue;
				if (count($dep->children)) {
					continue;
				}

				if ($p['full']) {
					$set_perms[] = array('department_id' => $did, 'person_id' => $agent->id, 'app' => 'tickets', 'name' => 'full', 'value' => 1);
				} else if ($p['assign']) {
					$set_perms[] = array('department_id' => $did, 'person_id' => $agent->id, 'app' => 'tickets', 'name' => 'assign', 'value' => 1);
				}
			}

			foreach ($dep_perm_overrides['chat'] as $did => $p) {
				if (!($dep = $chat_deps->getById($did))) continue;
				if (count($dep->children)) {
					continue;
				}

				if ($p['full']) {
					$set_perms[] = array('department_id' => $did, 'person_id' => $agent->id, 'app' => 'chat', 'name' => 'full', 'value' => 1);
				}
			}

			$this->db->executeUpdate("DELETE FROM department_permissions WHERE person_id = ?", array($agent->id));
			if ($set_perms) {
				$this->db->batchInsert('department_permissions', $set_perms, true);
			}
		}

		#-------------------------
		# Profile
		#-------------------------

		$data = array();
		if (isset($profile['signature_html'])) {
			$data['signature_html'] = $profile['signature_html'];
		}
		if (isset($profile['timezone'])) {
			$data['timezone'] = $profile['timezone'];
		}
		if (isset($profile['unset_picture'])) {
			$data['unset_picture'] = $profile['unset_picture'];
		}
		if (isset($profile['set_picture_blob'])) {
			$data['set_picture_blob'] = $profile['set_picture_blob'];
		}
		if ($data) {
			$this->_saveProfileData($agent, $data);
		}


		#-------------------------
		# Send welcome email
		#-------------------------

		// Send welcome email for new users
		if (!$id && !$skip_email) {
			$this->sendWelcomeEmail($agent);
		}

		#-------------------------
		# Return
		#-------------------------

		return $this->createApiCreateResponse(array(
			'person_id' => $agent->id
		), $this->generateUrl('api_agents_get', array('id' => $agent->id), UrlGeneratorInterface::ABSOLUTE_URL));
	}

	protected function sendWelcomeEmail(Person $agent)
	{
		$message = $this->container->getMailer()->createMessage();
		$message->setToPerson($agent);
		$message->setTemplate('DeskPRO:emails_agent:agent-welcome.html.twig', array('agent' => $agent));
		$attach = \Swift_Attachment::fromPath(DP_ROOT.'/src/Application/AgentBundle/Resources/assets/agent-quickstart/en_US.pdf', 'application/pdf');
		$attach->setFilename('Getting Started with DeskPRO.pdf');
		$message->attach($attach);
		$this->container->getMailer()->send($message);
	}

	/**
	 * @param int $num
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
		if ($max_agents && $current_agents+$num > $max_agents) {
			return $this->createApiErrorResponse('license_agents_reached', "Your license allows $max_agents. You cannot create $num more agents until you upgrade your license.");
		}

		return null;
	}

	####################################################################################################################
	# save-agent-profile
	####################################################################################################################

	public function saveAgentProfileAction($id = null)
	{
		$agent = $this->container->getAgentData()->get($id);

		if (!$agent) {
			throw $this->createNotFoundException();
		}

		$data = array();
		if ($this->in->checkIsset('signature_html')) {
			$data['signature_html'] = $this->in->getString('signature_html');
		}
		if ($this->in->checkIsset('timezone')) {
			$data['timezone'] = $this->in->getString('timezone');
		}
		if ($this->in->checkIsset('unset_picture')) {
			$data['unset_picture'] = $this->in->getBool('unset_picture');
		}
		if ($this->in->checkIsset('set_picture_blob')) {
			$data['set_picture_blob'] = $this->in->getString('set_picture_blob');
		}

		$this->_saveProfileData($agent, $data);
		return $this->createApiSuccessResponse();
	}

	private function _saveProfileData(Person $person, array $data)
	{
		if (isset($data['signature_html'])) {
			$signature_html = $data['signature_html'];
			$signature_html = \Orb\Util\Strings::trimHtml($signature_html);

			$regex          = '#<img[^>]+class="dp-signature-image" alt="([^"]+)"[^>]*>#i';
			$signature_html = preg_replace($regex, '$1', $signature_html);

			$signature_html = str_replace(array('<div', '</div>'), array('<p', '</p>'), $signature_html);
			$signature_html = preg_replace('/^<p>/', '<p class="dp-signature-start">', trim($signature_html));

			$signature = strip_tags($signature_html);

			$person->setPreference('agent.ticket_signature', $signature);
			$person->setPreference('agent.ticket_signature_html', $signature_html);
		}

		if (isset($data['timezone'])) {
			$person->timezone = $data['timezone'];
			$this->em->persist($person);
		}

		if (isset($data['unset_picture']) && $data['unset_picture'] && $person->picture_blob) {
			$old_blob = $person->picture_blob;
			$person->picture_blob = null;

			try {
				$this->container->getBlobStorage()->deleteBlobRecord($old_blob);
			} catch (\Exception $e) {}

			$this->em->persist($person);
		}

		if (isset($data['set_picture_blob'])) {
			if ($person->picture_blob) {
				$old_blob = $person->picture_blob;
				$person->picture_blob = null;

				try {
					$this->container->getBlobStorage()->deleteBlobRecord($old_blob);
				} catch (\Exception $e) {}

				$this->em->persist($person);
			}

			$blob = $this->em->getRepository('DeskPRO:Blob')->getByAuthCode($data['set_picture_blob']);
			if ($blob && $blob->isImage()) {
				$person->picture_blob = $blob;
				$this->em->persist($person);
			}
		}

		$this->em->flush();
	}


	####################################################################################################################
	# reset-password
	####################################################################################################################

	public function resetPasswordAction($id)
	{
		$agent = $this->container->getAgentData()->get($id);

		if (!$agent) {
			throw $this->createNotFoundException();
		}

		$password = $this->in->getString('set_password');
		if ($password) {
			/** @var \Application\DeskPRO\People\PasswordPolicyValidator $password_validator */
			$password_validator = $this->container->getSystemService('password_policy_validator');
			$error = '';
			if (!$password_validator->checkPassword($password, $agent, $error)) {
				return $this->createApiErrorInfoResponse('invalid_password', 'Password does not adhere to agent password policy.', array('error_code' => $error));
			}
		}
		if (!$password) {
			$password = Strings::randomPronounceable(20, 4);
		}

		if ($agent->password && $agent->password_scheme == 'bcrypt') {
			$history = new PasswordHistory();
			$history->person = $agent;
			$history->password_scheme = $agent->password_scheme;
			$history->password = $agent->password;
			$this->em->persist($history);
		}

		$agent->setPassword($password);
		$this->em->persist($agent);
		$this->em->flush();

		// Clear possible active sessions
		$this->db->delete('sessions', array('person_id' => $agent->id));

		$did_email = false;
		if (!$this->in->getBool('skip_email')) {
			$did_email = $agent->getPrimaryEmailAddress();
			$message = $this->container->getMailer()->createMessage();
			$message->setToPerson($agent);
			$message->setTemplate('DeskPRO:emails_agent:password-reset-alert.html.twig', array(
				'agent'        => $agent,
				'performer'    => $this->person,
				'new_password' => $password,
			));
			$this->container->getMailer()->send($message);
		}

		return $this->createSuccessResponse(array(
			'emailed' => $did_email
		));
	}


	####################################################################################################################
	# delete-agent
	####################################################################################################################

	public function deleteAgentAction($id, $mode)
	{
		$agent = $this->container->getAgentData()->get($id);

		if (!$agent) {
			throw $this->createNotFoundException();
		}

		if ($agent->id == $this->person->id) {
			return $this->createApiErrorResponse('no_delete_self', 'You cannot delete yourself');
		}

		$deleter = new AgentDelete($agent, $this->em);

		switch ($mode) {
			case 'user':
				$deleter->deleteToUser();
				break;

			case 'delete':
				$deleter->softDelete();
				break;

			default:
				throw $this->createNotFoundException();
		}

		return $this->createSuccessResponse();
	}


	####################################################################################################################
	# undelete-agent
	####################################################################################################################

	public function undeleteAgentAction($id)
	{
		$agent = $this->em->find('DeskPRO:Person', $id);

		if (!$agent || !$agent->is_agent || !$agent->is_deleted) {
			throw $this->createNotFoundException();
		}

		$agent->is_deleted = false;
		$this->em->persist($agent);
		$this->em->flush();

		return $this->createSuccessResponse(array('person_id' => $agent->id));
	}


	####################################################################################################################
	# generate-login-token
	####################################################################################################################

	public function generateLoginTokenAction($id)
	{
		$agent = $this->container->getAgentData()->get($id);

		if (!$agent) {
			throw $this->createNotFoundException();
		}

		$tmp = TmpData::create('admin_agent_login', array(
			'admin_id' => $this->person->getId(),
			'agent_id' => $agent->id
		), '+5 minutes');
		$this->em->persist($tmp);
		$this->em->flush();

		return $this->createApiResponse(array(
			'login_token'    => $tmp->getCode(),
			'valid_until'    => $tmp->date_expire->format('Y-m-d H:i:s'),
			'valid_until_ts' => $tmp->date_expire->getTimestamp(),
		));
	}


	####################################################################################################################
	# get-notify-prefs-tables
	####################################################################################################################

	public function getNotifyPrefsAction($id)
	{
		if ($id) {
			$agent = $this->container->getAgentData()->get($id);

			if (!$agent) {
				throw $this->createNotFoundException();
			}

			$loader    = new AgentNotifPrefsLoader($agent, $this->em);
			$prefs     = $loader->getPrefs();
			$filters   = $this->em->getRepository('DeskPRO:TicketFilter')->getFiltersForPerson($agent);
		} else {
			$agent = null;
			$prefs = new AgentNotifPrefs();
			$filters = $this->em->getRepository('DeskPRO:TicketFilter')->getFiltersForPerson($this->person);
		}

		$table_gen = new AgentNotifPrefsTable($prefs, $this->container->getTranslator());

		$sys_filters = array();
		$custom_filters = array();

		foreach ($filters as $f) {
			if ($f->sys_name) {
				if (strpos($f->sys_name, '_w_hold') !== false || strpos($f->sys_name, 'archive_') === 0) continue;
				$sys_filters[] = $f;
			} else {
				$custom_filters[] = $f;
			}
		}

		$tables = array();

		if ($agent) {
			$table_context = $this->person === $agent ? null : $agent;
		} else {
			$table_context = null;
		}

		$tables['sys_filters_email'] = $table_gen->buildSystemFiltersTable('email', $sys_filters, $table_context);
		$tables['sys_filters_alert'] = $table_gen->buildSystemFiltersTable('alert', $sys_filters, $table_context);

		if (!$id) {
			foreach ($tables['sys_filters_email']['rows'] as &$row) {
				foreach ($row['cols'] as &$col) {
					foreach ($col as &$p) {
						$p['value'] = true;
					}
				}
			}
			unset($row, $col, $p);

			foreach ($tables['sys_filters_alert']['rows'] as &$row) {
				foreach ($row['cols'] as &$col) {
					foreach ($col as &$p) {
						$p['value'] = true;
					}
				}
			}
			unset($row, $col, $p);
		}

		if ($custom_filters) {
			$tables['custom_filters_email'] = $table_gen->buildCustomFiltersTable('email', $custom_filters);
			$tables['custom_filters_alert'] = $table_gen->buildCustomFiltersTable('alert', $custom_filters);
		}

		foreach (Prefs::$apps as $app => $bool) {
			$method = 'build' . ucfirst($app) . 'Table';
			if (!method_exists($table_gen, $method)) {
				throw new \Exception('Wrong app name or table not exists');
			}
			$tables[$app]     = $table_gen->$method();
		}

		return $this->createApiResponse(array(
			'subs'         => $tables,
			'sub_options'  => array(
				'email' => $prefs->getFilterNotifyPrefs('email'),
				'alert' => $prefs->getFilterNotifyPrefs('alert'),
			),
			'mention_mode' => $prefs->getEmailMentionMode(),
		));
	}

	public function bulkCreateAgentsAction()
	{
		if ($filename = $this->in->getString('filename')) {
			return $this->bulkCreateAgentsFromFile($filename);
		}

		$agents = $this->in->getArrayValue('agents');
		$ret = array();

		foreach ($agents as $email => $agent) {
			$response = $this->saveAgent(null, $agent);
			$ret[trim($email)] = $response instanceof JsonResponse ? $response->getData() : $response->getContent();
		}

		return $this->createApiResponse($ret);
	}

	/**
	 * todo external mapper file-to-form
	 * @param $blobId
	 * @return Response
	 */
	protected function bulkCreateAgentsFromFile($blobId)
	{
		if (!$blob = $this->em->find('DeskPRO:Blob', $blobId)) {
			return $this->createApiErrorResponse('file_not_found', 'File not found');
		}

		$csv_file = dp_get_tmp_dir() . '/blob-' . $blob->getId() . '.csv';

		if (!file_exists($csv_file) || !is_readable($csv_file)) {
			file_put_contents($csv_file, $this->container->getBlobStorage()->copyBlobRecordToString($blob));
		}

		if (!file_exists($csv_file) || !is_readable($csv_file)) {
			return $this->createApiErrorResponse('file_not_found', 'File not found');
		}

		if (!$fp = fopen($csv_file, 'r')) {
			return $this->createApiErrorResponse('file_not_readable', 'Can\'t read file');
		}

		$prefs = new AgentNotifPrefs();
		$filters = $this->em->getRepository('DeskPRO:TicketFilter')->getFiltersForPerson($this->person);
		$defaultFilterSubs = array();
		$defaultOtherSubs = array();
		foreach ($filters as $filter) {
			$defaultFilterSubs[] = array(
				'filter_id' => $filter['id'],
				'email' => $prefs->getFilterNotifyTypes($filter, 'email'),
				'alert' => $prefs->getFilterNotifyTypes($filter, 'alert'),
			);
		}

		foreach (AgentNotifPrefs::$apps as $app => $bool) {
			$defaultOtherSubs[] = array(
				'type' => $app,
				'email' => array(),
				'alert' => array(),
			);
		}


		$row = fgetcsv($fp); // headers
		if (0 !== strpos($row[0], 'Email Address')) {
			fclose($fp);
			return $this->createApiErrorResponse('file_wrong_format', 'Wrong format of CSV file');
		}

		$delimeter = 1 === count($row) ? substr($row[0], 13, 1) : ',';

		$ret = array();
		while ($row = fgetcsv($fp, null, $delimeter)) {

			if (!$email = trim($row[0])) continue;

			$data = array(
				'email' => $email,
				'name' => $row[1],
				'agent_groups' => array(),
				'teams' => array(),
				'zones' => array(),
			);
			$filterSubs = $otherSubs = array();
			$profile = array(
				'signature_html' => $row[7],
			);

			if ('yes' === strtolower($row[4])) {
				$data['zones'][] = 'admin';
			}
			if ('yes' === strtolower($row[5])) {
				$data['zones'][] = 'reports';
			}
			foreach (explode(',', $row[2]) as $group) {
				if (!$group = (int) trim($group)) continue;
				$data['agent_groups'][] = $group;
			}
			foreach (explode(',', $row[3]) as $team) {
				if (!$team = (int) trim($team)) continue;
				$data['teams'][] = $team;
			}

			if (!$data['agent_groups']) {
				$ret[$email] = array('error_code' => 'validation_error', 'error_message' => 'At least 1 agent group required');
				continue;
			}

			// subscribe to default notifications
			if ('yes' === strtolower($row[6])) {
				$filterSubs = $defaultFilterSubs;
				$otherSubs = $defaultOtherSubs;
			}

			$response = $this->saveAgent(null, $data, $profile, $filterSubs, $otherSubs);
			$ret[$email] = $response instanceof JsonResponse ? $response->getData() : $response->getContent();
		}

		fclose($fp);

		return $this->createApiResponse($ret);
	}
}