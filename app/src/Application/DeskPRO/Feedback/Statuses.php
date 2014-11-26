<?php
namespace Application\DeskPRO\Feedback;

use Doctrine\ORM\EntityManager;

class Statuses
{
    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    protected $db;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->db = $em->getConnection();
    }

    public function getStatuses()
    {
    }
}
