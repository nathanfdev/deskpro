<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\App;

class TicketCharge extends AbstractEntityRepository
{
    public function getChargesForPerson(\Application\DeskPRO\Entity\Person $person, $limit = null, $offset = null)
    {
        if ($limit !== null) {
            $limit = intval($limit);
            if ($limit < 1) {
                $limit = null;
            }
        }

        $q = $this->getEntityManager()->createQuery('
            SELECT tc
            FROM DeskPRO:TicketCharge tc INDEX BY tc.id
            WHERE tc.person = ?0
            ORDER BY tc.id DESC
        ');

        if ($limit) {
            $q->setMaxResults($limit);
        }
        if ($offset) {
            $q->setFirstResult($offset);
        }

        return $q->execute([$person]);
    }

    public function getTotalChargesForPerson(\Application\DeskPRO\Entity\Person $person)
    {
        return App::getDb()->fetchAssoc('
            SELECT COUNT(*) AS count, SUM(charge_time) AS charge_time, SUM(amount) AS charge
            FROM ticket_charges
            WHERE person_id = ?
        ', [$person->id]);
    }

    public function getChargesForOrganization(\Application\DeskPRO\Entity\Organization $org, $limit = null, $offset = null)
    {
        if ($limit !== null) {
            $limit = intval($limit);
            if ($limit < 1) {
                $limit = null;
            }
        }

        $q = $this->getEntityManager()->createQuery('
            SELECT tc
            FROM DeskPRO:TicketCharge tc INDEX BY tc.id
            WHERE tc.organization = ?0
            ORDER BY tc.id DESC
        ');

        if ($limit) {
            $q->setMaxResults($limit);
        }
        if ($offset) {
            $q->setFirstResult($offset);
        }

        return $q->execute([$org]);
    }

    public function getTotalChargesForOrganization(\Application\DeskPRO\Entity\Organization $org)
    {
        return App::getDb()->fetchAssoc('
            SELECT COUNT(*) AS count, SUM(charge_time) AS charge_time, SUM(amount) AS charge
            FROM ticket_charges
            WHERE organization_id = ?
        ', [$org->id]);
    }
}
