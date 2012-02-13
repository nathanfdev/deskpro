<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Import
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Import\Importer\Step\Deskpro3;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;

class UsersStep extends AbstractDeskpro3Step
{
	/**
	 * @var array
	 */
	protected $custom_field_info = array();

	/**
	 * @var \Application\DeskPRO\CustomFields\FieldManager
	 */
	protected $fieldmanager;

	public static function getTitle()
	{
		return 'Import Users';
	}

	public function countPages()
	{
		$count = $this->getOldDb()->fetchColumn("SELECT COUNT(*) FROM user");
		if (!$count) {
			return 1;
		}

		return ceil($count / 1000);
	}

	public function run($page = 1)
	{
		$this->custom_field_info = $this->getOldDb()->fetchAll("SELECT * FROM user_def");
		$this->fieldmanager = $this->getContainer()->getSystemService('person_fields_manager');
		$this->fieldmanager->getFields();

		$batch = $this->getIdsBatch($page - 1);

		$ids = implode(',', $batch);
		if (!$ids) $ids = '0';

		$users = $this->getOldDb()->fetchAll("SELECT * FROM user WHERE id IN ($ids)");
		$sub_start_time = microtime(true);
		$this->logMessage("-- Processing batch {$page}");

		$this->getDb()->exec("SET unique_checks = 0");
		$this->getDb()->exec("SET foreign_key_checks = 0");

		foreach ($users as $u) {
			$this->getDb()->beginTransaction();

			try {
				$this->processUser($u);
				$this->getDb()->commit();
			} catch (\Exception $e) {
				$this->getDb()->rollback();
				throw $e;
			}
		}

		$this->getDb()->exec("SET unique_checks = 1");
		$this->getDb()->exec("SET foreign_key_checks = 1");

		$sub_end_time = microtime(true);
		$this->logMessage(sprintf("-- Done. Took %.3f seconds.", $sub_end_time-$sub_start_time));
	}


	/**
	 * Process a single user
	 * @param $user_id
	 */
	protected function processUser($user_info)
	{
		$user_id = $user_info['id'];

		#------------------------------
		# Make sure we havent already done them
		#------------------------------

		$check_exist = $this->getMappedNewId('user', $user_id);
		if ($check_exist) {
			$this->getLogger()->log("{$user_id} already mapped, skipping", 'DEBUG');
			return;
		}

		#------------------------------
		# Get the users info
		#------------------------------

		$user_map          = $this->getOldDb()->fetchAssoc("SELECT * FROM user_map WHERE localid = ? AND sourceid = 1", array($user_id));
		$user_deskpro      = $this->getOldDb()->fetchAssoc("SELECT * FROM user_deskpro WHERE id = ?", array($user_map['remoteid']));
		$user_company_id   = $this->getOldDb()->fetchColumn("SELECT company FROM user_member_company WHERE user = ? LIMIT 1", array($user_id));
		$user_emails       = $this->getOldDb()->fetchAll("SELECT * FROM user_email WHERE userid = ? AND validated = 1", array($user_id));
		$usergroup_ids     = $this->getOldDb()->fetchAllCol("SELECT usergroup FROM user_member_groups WHERE user = ?", array($user_id));

		#------------------------------
		# Make sure their email doesnt already match someone in the system
		#------------------------------

		$found = null;
		foreach ($user_emails as $email_info) {
			$check_exist_email = $this->getDb()->fetchColumn("SELECT person_id FROM people_emails WHERE email = ?", array($email_info['email']));
			if ($check_exist_email) {
				$found = $check_exist_email;
				break;
			}
		}

		if ($found) {
			$this->getLogger()->log("{$user_id} has an email that already exists, re-mapping ID to {$found} and skipping", 'DEBUG');

			// Map this id to the already existing user
			$this->saveMappedId('user', $user_id, $found);
			return;
		}

		#------------------------------
		# Re-create the user
		#------------------------------

		//---
		// Basic properties
		//---

		$person = new Person();
		$person->is_contact = true;
		$person->is_user = true;
		$person->is_confirmed = true;
		$person->name = $user_info['name'];
		$person->date_created = new \DateTime('@' . $user_info['date_registered']);
		if ($user_info['last_activity']) {
			$person->date_last_login = new \DateTime('@' . $user_info['last_activity']);
		}

		// "Secure passwords" was enabled, which means we have a salt and the password is hashed
		if ($user_deskpro['salt']) {
			$person->password_scheme = 'deskpro3';
			$person->setRawPassword($user_deskpro['password']);
			$person->salt = $user_deskpro['salt'];

		// "Secure passwords" was disabled, which means we dont have a salt and the password is plaintext
		// so we can just set a password normally and use DP4 scheme
		} else {
			$person->setPassword($user_deskpro['password']);
		}

		//---
		// Company
		//---

		if ($user_company_id) {
			$org = $this->getEm()->find('DeskPRO:Organization', $this->getMappedNewId('company', $user_company_id));
			if ($org) {
				$person->organization = $org;
			}
		}

		//---
		// Usergroups
		//---

		$done_ids = array();
		foreach ($usergroup_ids as $ug_id) {
			$usergroup = $this->getEm()->find('DeskPRO:Usergroup', $this->getMappedNewId('usergroup', $ug_id));
			if ($usergroup) {
				$done_ids[] = $usergroup->id;
				$person->usergroups->add($usergroup);
			}
		}

		// Also DP3 had the 'registered' group that was always added on demand
		$usergroup = $this->getEm()->find('DeskPRO:Usergroup', $this->getMappedNewId('usergroup_sys', 'registered'));
		if ($usergroup && !in_array($usergroup->id, $done_ids)) {
			$person->usergroups->add($usergroup);
		}


		//---
		// Email addresses
		//---

		$default_email = null;
		foreach ($user_emails as $email_info) {
			$email = new PersonEmail();
			$email->email = $email_info['email'];
			$email->date_validated = new \DateTime();

			if (!$default_email || $email_info['id'] == $user_info['default_emailid']) {
				$default_email = $email;
			}

			$person->addEmailAddress($email);
		}

		$person->primary_email = $default_email;

		//---
		// Custom fields
		//---

		$form_data = array();
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
					$form_data['field_' . $field->id] = $user_info[$name];
					break;

				case 'Application\\DeskPRO\\CustomFields\\Handler\\Choice':
					$val = str_replace('|||', '', $user_info[$name]);
					$new_val = $this->getMappedNewId('people_def_choice', $val);
					if ($new_val) {
						$form_data['field_' . $new_val] = 1;
					}
					break;

				case 'Application\\DeskPRO\\CustomFields\\Handler\\ChoiceMulti':
					$vals = explode('|||', $user_info[$name]);
					$new_vals = array();
					foreach ($vals as $val) {
						$new_val = $this->getMappedNewId('people_def_choice', $val);
						if ($new_val) {
							$form_data['field_' . $new_val] = 1;
						}
					}
					break;
			}
		}

		$this->getEm()->persist($person);
		$this->getEm()->flush();
		$this->saveMappedId('user', $user_id, $person->id);

		if ($form_data) {
			$this->fieldmanager->saveFormToObject($form_data, $person);
		}

		$this->getEm()->flush();
	}


	/**
	 * @param $page
	 * @return array
	 */
	protected function getIdsBatch($page)
	{
		$start = $page * 1000;
		$ids = $this->getOldDb()->fetchAllCol("SELECT id FROM user ORDER BY id ASC LIMIT $start, 1000");

		return $ids;
	}
}
