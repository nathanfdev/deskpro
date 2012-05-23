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

use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\OrganizationEmailDomain;

class CompaniesStep extends AbstractDeskpro3Step
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
		return 'Import Companies';
	}

	public function run($page = 1)
	{
		$count = $this->getOldDb()->fetchColumn("SELECT COUNT(*) FROM user_company");

		$this->logMessage(sprintf("Importing %d companies", $count));
		if (!$count) {
			return;
		}

		$this->custom_field_info = $this->getOldDb()->fetchAll("SELECT * FROM user_company_def");
		$this->fieldmanager = $this->getContainer()->getSystemService('org_fields_manager');
		$this->fieldmanager->getFields();

		$start_time = microtime(true);

		$page = 0;
		while ($batch = $this->getIdsBatch($page++)) {
			$sub_start_time = microtime(true);
			$this->logMessage("-- Processing batch {$page}");

			foreach ($batch as $cid) {
				$this->processCompany($cid);
			}

			$sub_end_time = microtime(true);
			$this->logMessage(sprintf("-- Done. Took %.3f seconds.", $sub_end_time-$sub_start_time));
		}

		$end_time = microtime(true);
		$this->logMessage(sprintf("Done all companies. Took %.3f seconds.", $end_time-$start_time));
	}

	public function processCompany($company_id)
	{
		#------------------------------
		# Make sure we havent already done them
		#------------------------------

		$check_exist = $this->getMappedNewId('company', $company_id);
		if ($check_exist) {
			$this->getLogger()->log("{$company_id} already mapped, skipping", 'DEBUG');
			return;
		}

		#------------------------------
		# Get info
		#------------------------------

		$company_info = $this->getOldDb()->fetchAssoc("SELECT * FROM user_company WHERE id = ?", array($company_id));
		$linked_rule = $this->getOldDb()->fetchAll("SELECT * FROM user_rules WHERE link_company = ?", array($company_id));

		$this->getDb()->beginTransaction();

		try {
			$org = new Organization();
			$org->name = $company_info['name'];

			$this->getEm()->persist($org);
			$this->getEm()->flush();

			$this->saveMappedId('company', $company_id, $org->id);

			if ($linked_rule && !empty($linked_rule['email_match'])) {
				foreach ($linked_rule['email_match'] as $email_match) {
					// We can only use domains now, but DP3 allowed full email addresses too
					$m = null;
					if (!preg_match('#^*@([a-zA-Z0-9\-\.]+)$#', $email_match, $m)) {
						continue;
					}

					$org_email_domain = new OrganizationEmailDomain();
					$org_email_domain->domain = $m[1];
					$org_email_domain->organization = $org;

					$this->getEm()->persist($org_email_domain);
				}
				$this->getEm()->flush();
			}

			//---
			// Usergroup relations
			//---

			$ug_rels = $this->getOldDb()->fetchAllCol("SELECT groupid FROM user_company2group WHERE companyid = ?", array(
				$company_info['id']
			));

			foreach ($ug_rels as $ug_id) {
				$new_ug_id = $this->getMappedNewId('usergroup', $ug_id);
				if (!$new_ug_id) continue;

				$this->getDb()->insert('organization2usergroups', array(
					'organization_id' => $org['id'],
					'usergroup_id'=> $new_ug_id
				));
			}

			//---
			// Custom fields
			//---

			foreach ($this->custom_field_info as $field_info) {
				$name = $field_info['name'];
				if (!isset($company_info[$name]) || !$company_info[$name]) {
					continue;
				}

				$field = $this->fieldmanager->getFieldFromId($this->getMappedNewId('org_def', $field_info['id']));
				if (!$field) {
					continue;
				}

				$data = null;
				switch ($field->handler_class) {
					case 'Application\\DeskPRO\\CustomFields\\Handler\\Text':
					case 'Application\\DeskPRO\\CustomFields\\Handler\\Textarea':
						$this->getDb()->insert('custom_data_organizations', array(
							'organization_id' => $org['id'],
							'field_id' => $field->id,
							'input' => $company_info[$name]
						));
						break;

					case 'Application\\DeskPRO\\CustomFields\\Handler\\Choice':
						$val = str_replace('|||', '', $company_info[$name]);
						$new_val = $this->getMappedNewId('org_def_choice', $field_info['id'].'_'.$val);
						if ($new_val) {
							$this->getDb()->insert('custom_data_organizations', array(
								'organization_id' => $org['id'],
								'field_id' => $new_val,
								'value' => 1
							));
						}
						break;

					case 'Application\\DeskPRO\\CustomFields\\Handler\\ChoiceMulti':
						$vals = explode('|||', $company_info[$name]);
						foreach ($vals as $val) {
							$new_val = $this->getMappedNewId('org_def_choice', $field_info['id'].'_'.$val);
							if ($new_val) {
								$this->getDb()->insert('custom_data_organizations', array(
									'organization_id' => $org['id'],
									'field_id' => $new_val,
									'value' => 1
								));
							}
						}
						break;
				}
			}

			$this->getDb()->commit();
		} catch (\Exception $e) {
			$this->getDb()->rollback();
		}
	}


	/**
	 * @param $page
	 * @return array
	 */
	protected function getIdsBatch($page)
	{
		$start = $page * 1000;
		$ids = $this->getOldDb()->fetchAllCol("SELECT id FROM user_company ORDER BY id ASC LIMIT $start, 1000");

		return $ids;
	}
}
