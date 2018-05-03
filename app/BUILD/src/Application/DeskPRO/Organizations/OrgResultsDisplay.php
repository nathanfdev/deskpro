<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Organizations;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
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
     * @var array
     */
    protected $all_fields_data;

    /**
     * @param \Application\DeskPRO\Entity\Organization[] $orgs
     */
    public function __construct(array $orgs)
    {
        $this->orgs       = $orgs;
        $this->orgs_count = count($orgs);
        $this->org_ids    = Arrays::flattenToIndex($this->orgs, 'id');

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
        if ($this->all_labels !== null) {
            return $this->all_labels;
        }

        if (!$this->orgs_count) {
            $this->all_labels = [];

            return $this->all_labels;
        }

        $org_ids = implode(',', $this->org_ids);

        $this->all_labels = $this->db->fetchAllGrouped("
            SELECT organization_id, label
            FROM labels_organizations
            WHERE organization_id IN ($org_ids)
        ", [], 'organization_id', null, 'label');

        return $this->all_labels;
    }

    /**
     * Get an array of labels applied to an org.
     *
     * @param \Application\DeskPRO\Entity\Organization $org
     *
     * @return array
     */
    public function getOrgLabels(Organization $org)
    {
        $this->getAllLabels();

        return empty($this->all_labels[$org->id]) ? [] : $this->all_labels[$org->id];
    }

    /**
     * Check if an org has labels.
     *
     * @param \Application\DeskPRO\Entity\Organization $org
     *
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
        if ($this->org_member_counts !== null) {
            return $this->org_member_counts;
        }

        $this->org_member_counts = $this->db->fetchAllKeyValue('
            SELECT organization_id, COUNT(*)
            FROM people
            WHERE organization_id IN(?) AND is_deleted = 0
            GROUP BY organization_id
        ', [$this->org_ids], [Connection::PARAM_INT_ARRAY]);

        return $this->org_member_counts;
    }

    /**
     * Get the number of tickets submitted by a user.
     *
     * @param \Application\DeskPRO\Entity\Organization $org
     *
     * @return int
     */
    public function getOrgMemberCount(Organization $org)
    {
        $this->getAllOrgMemberCounts();

        return isset($this->org_member_counts[$org->id]) ? $this->org_member_counts[$org->id] : 0;
    }

    public function getFieldsData(Organization $org)
    {
        $this->getAllFieldsData();

        return isset($this->all_fields_data[$org->getId()]) ? $this->all_fields_data[$org->getId()] : [];
    }

    public function getAllFieldsData()
    {
        if ($this->all_fields_data !== null) {
            return $this->all_fields_data;
        }
        $data = $this->em->createQuery('
            SELECT d, def, root_def
            FROM DeskPRO:CustomDataOrganization AS d
            LEFT JOIN d.field def
            LEFT JOIN d.root_field root_def
            WHERE d.organization IN (?0)
        ')->execute([array_values($this->org_ids)]);

        $this->all_fields_data = [];
        foreach ($data as $d) {
            $tid = $d->organization['id'];
            if (!isset($this->all_fields_data[$tid])) {
                $this->all_fields_data[$tid] = [];
            }

            $this->all_fields_data[$tid][] = $d;
        }

        return $this->all_fields_data;
    }
}
