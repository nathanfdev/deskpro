<?php

/**
 * DeskPRO.
 *
 * @category Mail
 */

namespace Application\DeskPRO\Organizations;

use Application\DeskPRO\Entity\Organization;
use Doctrine\ORM\EntityManager;

class OrgEditManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    protected $db;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->db = $em->getConnection();
    }

    public function deleteOrganization(Organization $org)
    {
        $this->em->beginTransaction();

        try {
            $this->em->remove($org);
            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }
}
