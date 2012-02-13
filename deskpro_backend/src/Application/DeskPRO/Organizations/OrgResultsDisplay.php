<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Organizations
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */
namespace Application\DeskPRO\Organizations;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Organization;
use Orb\Util\Arrays;

class OrgResultsDisplay
{
	/**
	 * @var \Application\DeskPRO\Entity\Organization[]
	 */
	protected $orgs;

	/**
	 * @var array
	 */
	protected $org_ids;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	protected $db;

	/**
	 * @var int
	 */
	protected $orgs_count;

	/**
	 * @var array
	 */
	protected $all_labels;

	/**
	 * @var array
	 */
	protected $org_member_counts;

	/**
	 * @param \Application\DeskPRO\Entity\Organization[] $orgs
	 */
	public function __construct(array $orgs)
	{
		$this->people = $orgs;
		$this->orgs_count = count($orgs);
		$this->org_ids = Arrays::flattenToIndex($this->people, 'id');


		$this->em = App::getOrm();
		$this->db = $this->em->getConnection();
	}


	/**
	 * @return int
	 */
	public function getCount()
	{
		return $this->orgs_count;
	}


	/**
	 * @return \Application\DeskPRO\Entity\Organization[]
	 */
	public function getOrganizations()
	{
		return $this->orgs;
	}


	/**
	 * @return array
	 */
	public function getAllLabels()
	{
		if ($this->all_labels !== null) return $this->all_labels;

		if (!$this->orgs_count) {
			$this->all_labels = array();
			return $this->all_labels;
		}

		$org_ids = implode(',', $this->org_ids);

		$this->all_labels = $this->db->fetchAllGrouped("
			SELECT organization_id, label
			FROM labels_organizations
			WHERE organization_id IN ($org_ids)
		", array(), 'organization_id', null, 'label');

		return $this->all_labels;
	}


	/**
	 * Get an array of labels applied to an org
	 *
	 * @param \Application\DeskPRO\Entity\Organization $org
	 * @return array
	 */
	public function getOrgLabels(Organization $org)
	{
		$this->getAllLabels();
		return empty($this->all_labels[$org->id]) ? array() : $this->all_labels[$org->id];
	}


	/**
	 * Check if an org has labels
	 *
	 * @param \Application\DeskPRO\Entity\Organization $org
	 * @return bool
	 */
	public function hasOrgLabels(Organization $org)
	{
		$this->getAllLabels();
		return !empty($this->all_labels[$org->id]);
	}


	/**
	 * @return array
	 */
	public function getAllOrgMemberCounts()
	{
		if ($this->org_member_counts !== null) return $this->org_member_counts;

		$org_ids = implode(',', $this->org_ids);

		$this->org_member_counts = $this->db->fetchAllKeyValue("
			SELECT organization_id, COUNT(*)
			FROM people
			WHERE organization_id IN($org_ids)
			GROUP BY organization_id
		");

		return $this->org_member_counts;
	}


	/**
	 * Get the number of tickets submitted by a user.
	 *
	 * @param \Application\DeskPRO\Entity\Organization $org
	 * @return int
	 */
	public function getOrgMemberCount(Organization $org)
	{
		$this->getAllOrgMemberCounts();
		return isset($this->org_member_counts[$org->id]) ? $this->org_member_counts[$org->id] : 0;
	}
}
