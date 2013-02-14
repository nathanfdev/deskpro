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

namespace Application\DeskPRO\Import\Importer\Step\Zendesk;

use Application\DeskPRO\Entity\Person;

class UsersStep extends AbstractZendeskStep
{
	const PERPAGE = 100;

	/**
	 * @var array
	 */
	protected $custom_field_info = array();

	/**
	 * @var array
	 */
	protected $checked_org_ids = array();

	public static function getTitle()
	{
		return 'Import Users';
	}

	public function countPages()
	{
		$res = $this->zd->sendGet('users', array('per_page' => 1));
		$count = (int)$res->get('count');

		$this->logMessage(sprintf("%d records in %d pages", $count, ceil($count / self::PERPAGE)));

		return ceil($count / self::PERPAGE);
	}

	public function run($page = 1)
	{
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
	}


	/**
	 * Process a single user
	 * @param $user_id
	 */
	protected function processUser($user_info)
	{
		$user_id = $user_info['id'];

		#----------------------------------------
		# Insert user record
		#----------------------------------------

		$insert_person = array();
		$insert_person['id']               = $user_id;
		$insert_person['date_created']     = date('Y-m-d H:i:s', strtotime($user_info['created_at']));
		$insert_person['secret_string']    = \Orb\Util\Strings::random(40);
		$insert_person['timezone']         = 'UTC';
		$insert_person['salt']             = \Orb\Util\Strings::random(40);
		$insert_person['is_contact']       = 1;
		$insert_person['is_user']          = 1;
		$insert_person['is_confirmed']     = 1;
		$insert_person['name']             = $user_info['name'];

		if ($user_info['last_login_at']) {
			$insert_person['date_last_login'] = date('Y-m-d H:i:s', strtotime($user_info['last_login_at']));
		}

		$notes = '';
		if ($user_info['details']) {
			$notes = $user_info['details'] . "\n\n\n";
		}
		if ($user_info['notes']) {
			$notes .= $user_info['notes'];
		}

		$insert_person['notes'] = trim($notes);
		if ($this->verifyOrgId($user_info['organization_id'])) {
			$insert_person['organization_id'] = $user_info['organization_id'];
		}

		if (!$user_info['active']) {
			$insert_person['is_deleted'] = 1;
		}
		if ($user_info['suspended']) {
			$insert_person['is_disabled'] = 1;
		}

		if ($user_info['role'] == 'agent' || $user_info['role'] == 'admin') {
			$insert_person['is_agent'] = true;
			$insert_person['can_agent'] = true;

			if ($user_info['alias']) {
				$insert_person['override_display_name'] = $user_info['alias'];
			}

			if ($user_info['role'] == 'admin') {
				$insert_person['can_admin'] = true;
				$insert_person['can_billing'] = true;
				$insert_person['can_reports'] = true;
			}
		}

		$this->db->insert('people', $insert_person);

		#------------------------------
		# Insert labels
		#------------------------------

		if ($user_info['tags']) {
			$insert_bulk = array();
			foreach ($user_info['tags'] as $tag) {
				$row = array();
				$row['person_id'] = $user_info;
				$row['label'] = strtolower($tag);

				$insert_bulk[] = $row;
			}

			$this->db->batchInsert('labels_people', $insert_bulk, true);
		}

		#----------------------------------------
		# Insert email and other contact info
		#----------------------------------------

		$primary_email_id = null;
		$insert_bulk_emails   = array();
		$insert_contact_data  = array();

		foreach ($user_info['identities'] as $ident) {
			switch ($ident['type']) {
				case 'email':
					$email = strtolower($ident['value']);
					list (, $domain) = explode('@', $email);

					if ($primary_email_id === null) {
						$primary_email_id = $ident['id'];
					}

					if ($user_info['email'] == $ident['email']) {
						$primary_email_id = $ident['id'];
					}

					$insert_bulk_emails[] = array(
						'id'             => $ident['id'],
						'person_id'      => $user_id,
						'email'          => $email,
						'email_domain'   => $domain,
						'is_validated'   => $ident['verified'] ? 1 : 0,
						'date_created'   => date('Y-m-d H:i:s', strtotime($ident['created_at'])),
						'date_validated' => $ident['verified'] ? date('Y-m-d H:i:s', strtotime($ident['updated_at'])) : null
					);
					break;

				case 'twitter':
					$insert_contact_data[] = array(
						'person_id'    => $user_id,
						'contact_type' => 'twitter',
						'field_1'      => $ident['value'],
						'field_2'      => null
					);
					break;

				case 'facebook':
					$insert_contact_data[] = array(
						'person_id'    => $user_id,
						'contact_type' => 'facebook',
						'field_1'      => 'http://facebook.com/people/' . $ident['value'],
						'field_2'      => $ident['value']
					);
					break;

				case 'phone':
					$insert_contact_data[] = array(
						'person_id'    => $user_id,
						'contact_type' => 'phone',
						'field_1'      => null,
						'field_2'      => $ident['value'],
						'field_3'      => 'phone'
					);
					break;
			}
		}

		if ($insert_bulk_emails) {
			$this->db->batchInsert('people_emails', $insert_bulk_emails, true);

			if ($primary_email_id) {
				$this->db->update(
					'people',
					array('primary_email_id' => $primary_email_id),
					array('id' => $user_id)
				);
			}
		}

		if ($insert_contact_data) {
			$this->db->batchInsert('people_contact_data', $insert_contact_data, true);
		}
	}


	/**
	 * @param $page
	 * @return array
	 */
	protected function getBatch($page)
	{
		$res = $this->zd->sendGet('users', array('per_page' => self::PERPAGE, 'page' => $page));

		$batch = $res->get('users');

		return $batch;
	}
}
