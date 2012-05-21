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
 * @subpackage Import
 */

namespace Application\DeskPRO\Import\Importer\Step\Deskpro3;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;

class UsersStep extends AbstractDeskpro3Step
{
	const PERPAGE = 1000;

	/**
	 * @var array
	 */
	protected $custom_field_info = array();

	/**
	 * @var \Application\DeskPRO\CustomFields\FieldManager
	 */
	protected $fieldmanager;

	/**
	 * @var \Application\DeskPRO\Entity\Usersource[]
	 */
	protected $usersources;

	public static function getTitle()
	{
		return 'Import Users';
	}

	public function countPages()
	{
		$count = $this->olddb->fetchColumn("SELECT id FROM user ORDER BY id DESC LIMIT 1");
		if (!$count) {
			return 1;
		}

		return ceil($count / self::PERPAGE);
	}

	public function preRunAll()
	{
		$this->importer->removeTableIndexes('people');
		$this->importer->removeTableIndexes('custom_data_person');
		$this->importer->removeTableIndexes('person2usergroups');
		$this->importer->removeTableIndexes('people_emails');
	}

	public function postRunAll()
	{
		$this->importer->restoreTableIndexes('people');
		$this->importer->restoreTableIndexes('custom_data_person');
		$this->importer->restoreTableIndexes('person2usergroups');
		$this->importer->restoreTableIndexes('people_emails');
	}

	public function run($page = 1)
	{
		if ($page == 1) {
			$this->preRunAll();
		}

		$this->custom_field_info = $this->olddb->fetchAll("SELECT * FROM user_def");
		$this->fieldmanager = $this->getContainer()->getSystemService('person_fields_manager');
		$this->fieldmanager->getFields();

		$this->usersources = $this->getEm()->getRepository('DeskPRO:Usersource')->getAllUsersources(false);

		$sub_start_time = microtime(true);
		$this->logMessage("-- Processing batch {$page}");

		$users = $this->getBatch($page);
		$this->db->exec("SET unique_checks = 0");
		$this->db->exec("SET foreign_key_checks = 0");

		$this->db->beginTransaction();
		try {
			foreach ($users as $u) {
				$this->processUser($u);
			}
			$this->importer->flushSaveMappedIdBuffer();
			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}

		$this->db->exec("SET unique_checks = 1");
		$this->db->exec("SET foreign_key_checks = 1");

		$sub_end_time = microtime(true);
		$this->logMessage(sprintf("-- Done. Took %.3f seconds.", $sub_end_time-$sub_start_time));

		if ($page >= $this->countPages()) {
			$this->postRunAll();
		}
	}


	/**
	 * Process a single user
	 * @param $user_id
	 */
	protected function processUser($user_batch)
	{
		$user_info = $user_batch['user'];

		$user_id = $user_info['id'];

		#------------------------------
		# Make sure we havent already done them
		#------------------------------

		$check_exist = $this->getMappedNewId('user', $user_id);
		if ($check_exist) {
			return;
		}

		#------------------------------
		# Get the users info
		#------------------------------

		$user_map          = $user_batch['user_map'];
		$user_deskpro      = $user_batch['user_deskpro'];
		$user_company_id   = $user_batch['user_company_id'];
		$user_emails       = $user_batch['user_email'];
		$usergroup_ids     = $user_batch['usergroup_ids'];

		#------------------------------
		# Make sure their email doesnt already match someone in the system
		#------------------------------

		// The only way there can be a dupe is if theres an agent that was imported before,
		// so we inserted a map in tech_email(email, agentid) in the TechsStep
		// This is so we could get rid of the indexes on people_emails, we dont need it since we dont look up on it

		$found = null;
		foreach ($user_emails as $email_info) {
			$check_exist_email = $this->getMappedNewId('tech_email', strtolower($email_info['email']));
			if ($check_exist_email) {
				$found = $check_exist_email;
				break;
			}
		}

		if ($found) {
			$this->getLogger()->log("{$user_id} has an email that already exists, re-mapping ID to {$found} and skipping", 'DEBUG');

			// Map this id to the already existing user
			$this->saveMappedId('user', $user_id, $found, true);
			return;
		}

		#------------------------------
		# Re-create the user
		#------------------------------

		//---
		// Basic properties
		//---

		$insert_person = array();
		$insert_person['date_created']     = date('Y-m-d H:i:s', $user_info['date_registered']);
		$insert_person['secret_string']    = \Orb\Util\Strings::random(40);
		$insert_person['timezone']         = 'UTC';
		$insert_person['salt']             = \Orb\Util\Strings::random(40);
		$insert_person['is_contact']       = 1;
		$insert_person['is_user']          = 1;
		$insert_person['is_confirmed']     = 1;
		$insert_person['name']             = $user_info['name'];

		if ($user_info['awaiting_register_validate_tech']) {
			$insert_person['is_agent_confirmed'] = 0;
		} else {
			$insert_person['is_agent_confirmed'] = 1;
		}

		$name_parts = \Application\DeskPRO\People\Util::guessNameParts($user_info['name'], isset($user_emails[0]) ? $user_emails[0]['email'] : null);
		$insert_person['first_name'] = $name_parts[0];
		$insert_person['last_name'] = $name_parts[1];

		if (!$insert_person['name']) {
			$insert_person['name'] = trim(($insert_person['first_name'] ?: '') . ' ' . ($insert_person['last_name'] ?: ''));
		}

		if ($user_info['last_activity']) {
			$insert_person['date_last_login'] = date('Y-m-d H:i:s', $user_info['last_activity']);
		}

		//---
		// Company
		//---

		if ($user_company_id) {
			if ($this->getMappedNewId('company', $user_company_id)) {
				$insert_person['organization_id'] = $this->getMappedNewId('company', $user_company_id);
			}
		}

		if ($user_deskpro) {
			// "Secure passwords" was enabled, which means we have a salt and the password is hashed
			if ($user_deskpro['salt']) {
				$insert_person['password_scheme'] = 'deskpro3';
				$insert_person['password'] = $user_deskpro['password'];
				$insert_person['salt'] = $user_deskpro['salt'];

			// "Secure passwords" was disabled, which means we dont have a salt and the password is plaintext
			// so we can just set a password normally and use DP4 scheme
			} else {
				$insert_person['password'] = sha1($insert_person['salt'] . $user_deskpro['password']);
			}
		} else {
			$insert_person['password'] = null;
		}

		$this->db->insert('people', $insert_person);
		$insert_person['id'] = $this->db->lastInsertId();

		$this->saveMappedId('user', $user_id, $insert_person['id'], true);


		//---
		// Usergroups
		//---

		$done_ids = array();
		foreach ($usergroup_ids as $ug_id) {
			$new_ug_id = $this->getMappedNewId('usergroup', $ug_id);
			if ($new_ug_id) {
				$this->db->insert('person2usergroups', array('person_id' => $insert_person['id'], 'usergroup_id' => $new_ug_id));
				$done_ids[$new_ug_id] = 1;
			}
		}

		// Also DP3 had the 'registered' group that was always added on demand
		$new_ug_id = $this->getMappedNewId('usergroup_sys', 'registered');
		if ($new_ug_id && !isset($done_ids[$new_ug_id])) {
			$this->db->insert('person2usergroups', array('person_id' => $insert_person['id'], 'usergroup_id' => $new_ug_id));
		}

		unset($done_ids);


		//---
		// Email addresses
		//---

		$default_email_id = null;
		foreach ($user_emails as $email_info) {

			$insert_email = array();
			$insert_email['person_id'] = $insert_person['id'];
			$insert_email['email'] = $email_info['email'];
			list (,$insert_email['email_domain']) = explode('@', $email_info['email'], 2);
			$insert_email['date_validated'] = date('Y-m-d H:i:s');
			$insert_email['date_created'] = date('Y-m-d H:i:s');
			$insert_email['is_validated'] = date('Y-m-d H:i:s');

			$this->db->insert('people_emails', $insert_email);

			if (!$default_email_id || $email_info['id'] == $user_info['default_emailid']) {
				$default_email_id = $this->db->lastInsertId();
			}
		}

		$this->db->update('people', array('primary_email_id' => $default_email_id), array('id' => $insert_person['id']));

		//---
		// Re-create the map
		//---

		foreach ($user_map as $um) {
			if ($um['sourceid'] != 1) {
				$new_usersource = $this->usersources[$this->getMappedNewId('usersource', $um['sourceid'])];
				if ($new_usersource) {
					$new_map = null;
					switch ($new_usersource->source_type) {
						case 'db_table_php_password_check':
						case 'ez_publish':
						case 'os_commerce':
						case 'php_bb_2':
						case 'php_bb_3':
						case 'vbulletin':
							$new_map = array(
								'person_id'         => $insert_person['id'],
								'usersource_id'     => $new_usersource->id,
								'identity'          => $um['remoteid'],
								'identity_friendly' => $um['username'],
							);
							break;
						case 'dp3_ldap':
							$new_map = array(
								'person_id'         => $insert_person['id'],
								'usersource_id'     => $new_usersource->id,
								'identity'          => $um['remoteid'],
								'identity_friendly' => $um['username'],
							);
							break;
					}

					if ($new_map) {
						$new_map['data'] = 'a:0:{}';
						$new_map['created_at'] = date('Y-m-d H:i:s', $user_info['date_registered']);

						$this->db->insert('person_usersource_assoc', $new_map);
					}
				}
			}
		}


		//---
		// Custom fields
		//---

		foreach ($this->custom_field_info as $field_info) {
			$name = $field_info['name'];
			if (!isset($user_info[$name]) || !$user_info[$name]) {
				continue;
			}

			$field = $this->fieldmanager->getFieldFromId($this->getMappedNewId('people_def', $field_info['id']));
			if (!$field) {
				continue;
			}

			$data = null;
			switch ($field->handler_class) {
				case 'Application\\DeskPRO\\CustomFields\\Handler\\Text':
				case 'Application\\DeskPRO\\CustomFields\\Handler\\Textarea':
					$this->db->insert('custom_data_person', array(
						'person_id' => $insert_person['id'],
						'field_id' => $field->id,
						'input' => $user_info[$name]
					));
					break;

				case 'Application\\DeskPRO\\CustomFields\\Handler\\Choice':
					$val = str_replace('|||', '', $user_info[$name]);
					$new_val = $this->getMappedNewId('people_def_choice', $field_info['id'].'_'.$val);
					if ($new_val) {
						$this->db->insert('custom_data_person', array(
							'person_id' => $insert_person['id'],
							'field_id' => $new_val,
							'value' => 1
						));
					}
					break;

				case 'Application\\DeskPRO\\CustomFields\\Handler\\ChoiceMulti':
					$vals = explode('|||', $user_info[$name]);
					foreach ($vals as $val) {
						$new_val = $this->getMappedNewId('people_def_choice', $field_info['id'].'_'.$val);
						if ($new_val) {
							$this->db->insert('custom_data_person', array(
								'person_id' => $insert_person['id'],
								'field_id' => $new_val,
								'value' => 1
							));
						}
					}
					break;
			}
		}
	}


	/**
	 * @param $page
	 * @return array
	 */
	protected function getBatch($page)
	{
		$start = (($page-1) * self::PERPAGE) + 1;
		$end   = $page * self::PERPAGE;

		$between_where = "BETWEEN $start AND $end";

		#------------------------------
		# Fetch ticket
		#------------------------------

		$q = $this->olddb->query("SELECT * FROM user WHERE id $between_where");
		$q->execute();

		$batch = array();

		while ($user = $q->fetch(\PDO::FETCH_ASSOC)) {
			$batch[$user['id']] = array(
				'user' => $user,
				'user_map' => array(),
				'user_deskpro' => array(),
				'user_company_id' => 0,
				'user_email' => array(),
				'usergroup_ids' => array(),
			);
		}
		$q->closeCursor();
		unset($q);

		#------------------------------
		# Fetch user_map
		#------------------------------

		$q = $this->olddb->query("SELECT * FROM user_map WHERE localid $between_where AND sourceid = 1");
		$q->execute();

		$remote_ids = array();
		$remote_id_map = array();
		while ($r = $q->fetch(\PDO::FETCH_ASSOC)) {
			if (!isset($batch[$r['localid']])) continue;
			$batch[$r['localid']]['user_map'][] = $r;
			$remote_ids[] = $r['remoteid'];
			$remote_id_map[$r['remoteid']] = $r['localid'];
		}
		$q->closeCursor();
		unset($q);

		#------------------------------
		# Fetch user_deskpro
		#------------------------------

		if ($remote_ids) {

			$remote_ids = implode(',', $remote_ids);

			$q = $this->olddb->query("SELECT * FROM user_deskpro WHERE id IN ($remote_ids)");
			$q->execute();

			while ($r = $q->fetch(\PDO::FETCH_ASSOC)) {
				if (!isset($remote_id_map[$r['id']])) continue;
				$localid = $remote_id_map[$r['id']];
				if (!isset($batch[$localid])) continue;
				$batch[$localid]['user_deskpro'] = $r;
			}
			$q->closeCursor();
			unset($q);
		}

		unset($remote_ids, $remote_id_map);

		#------------------------------
		# Fetch user_company_id
		#------------------------------

		$q = $this->olddb->query("SELECT user, company FROM user_member_company WHERE user $between_where");
		$q->execute();

		while ($r = $q->fetch(\PDO::FETCH_ASSOC)) {
			if (!isset($batch[$r['user']])) continue;
			$batch[$r['user']]['user_company_id'] = $r['company'];
		}
		$q->closeCursor();
		unset($q);

		#------------------------------
		# Fetch user_email
		#------------------------------

		$q = $this->olddb->query("SELECT * FROM user_email WHERE userid $between_where");
		$q->execute();

		while ($r = $q->fetch(\PDO::FETCH_ASSOC)) {
			if (!isset($batch[$r['userid']])) continue;
			$batch[$r['userid']]['user_email'][] = $r;
		}
		$q->closeCursor();
		unset($q);

		#------------------------------
		# Fetch usergroup_ids
		#------------------------------

		$q = $this->olddb->query("SELECT user, usergroup FROM user_member_groups WHERE user $between_where");
		$q->execute();

		while ($r = $q->fetch(\PDO::FETCH_ASSOC)) {
			if (!isset($batch[$r['user']])) continue;
			$batch[$r['user']]['usergroup_ids'][] = $r['usergroup'];
		}
		$q->closeCursor();
		unset($q);

		return $batch;
	}
}
