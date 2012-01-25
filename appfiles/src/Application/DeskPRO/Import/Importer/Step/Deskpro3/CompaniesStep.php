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

use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\OrganizationEmailDomain;

class CompaniesStep extends AbstractDeskpro3Step
{
	public static function getTitle()
	{
		return 'Import Companies';
	}

	public function run()
	{
		$count = $this->getOldDb()->fetchColumn("SELECT COUNT(*) FROM user_company");

		$this->logMessage(sprintf("Importing %d companies", $count));
		if (!$count) {
			return;
		}

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
		$ids = $this->getOldDb()->fetchAllCol("SELECT id FROM company ORDER BY id ASC LIMIT $start, 1000");

		return $ids;
	}
}
