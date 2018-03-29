<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity;

class Sla extends AbstractEntityRepository
{
    /** @var array|null */
    protected $_all_slas = null;

    /**
     * @return \Application\DeskPRO\Entity\Sla[]
     */
    public function getAllSlas()
    {
        if ($this->_all_slas === null) {
            $this->_all_slas = $this->getEntityManager()->createQuery('
                SELECT s
                FROM DeskPRO:Sla s INDEX BY s.id
                ORDER BY s.title
            ')->execute();
        }

        return $this->_all_slas;
    }

    /**
     * @return \Application\DeskPRO\Entity\Sla[]
     */
    public function getAutoSlas()
    {
        return $this->_em->createQuery("
            SELECT s
            FROM DeskPRO:Sla s
            WHERE s.apply_type IN ('all', 'terms')
        ")->execute();
    }

    public function clearSlaCache()
    {
        $this->_all_slas = null;
    }

    /**
     * @return \Application\DeskPRO\Entity\Sla[]
     */
    public function getPersonOrgAssociableSlas()
    {
        $slas = $this->getAllSlas();
        foreach ($slas as $k => $sla) {
            if ($sla->apply_type != 'people_orgs') {
                unset($slas[$k]);
            }
        }

        return $slas;
    }

    /**
     * @return \Application\DeskPRO\Entity\Sla[]
     */
    public function getAddableSlas(Entity\Ticket $ticket)
    {
        $slas = $this->getAllSlas();
        if (!$slas) {
            return [];
        }

        foreach ($slas as $key => $sla) {
            if ($sla->apply_type != 'manual') {
                unset($slas[$key]);
            }
        }

        return $slas;
    }

    public function getSlaTitles(array $ids = null)
    {
        $output = [];
        foreach ($this->getAllSlas() as $sla) {
            if (!is_array($ids) || in_array($sla->id, $ids)) {
                $output[$sla->id] = $sla->title;
            }
        }

        return $output;
    }

    public function hasSlas()
    {
        return count($this->getAllSlas()) > 0;
    }

    public function doesSlaApplyToPerson(Entity\Sla $sla, Entity\Person $person)
    {
        $id = $this->getEntityManager()->getConnection()->fetchColumn('
            SELECT sla_id
            FROM sla_people
            WHERE sla_id = ? AND person_id = ?
        ', [$sla->id, $person->id]);

        return $id ? true : false;
    }

    public function doesSlaApplyToOrganization(Entity\Sla $sla, Entity\Organization $organization)
    {
        $id = $this->getEntityManager()->getConnection()->fetchColumn('
            SELECT sla_id
            FROM sla_organizations
            WHERE sla_id = ? AND organization_id = ?
        ', [$sla->id, $organization->id]);

        return $id ? true : false;
    }
}
