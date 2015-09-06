<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

namespace Application\ImportBundle\Reader\DeskPRO;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity;
use Application\DeskPRO\EntityRepository;
use Application\DeskPRO\ORM\EntityManager;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\DriverManager;
use Application\ImportBundle\Reader\AbstractReader;

/**
 * Class DeskPROReader
 * @package Application\ImportBundle\Reader\DeskPRO
 */
class DeskPROReader extends AbstractReader
{
    /**
     * @var DeskproContainer
     */
    protected $container;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Constructor
     *
     * @param DeskPROConfig    $config
     * @param DeskproContainer $container
     */
    public function __construct(DeskPROConfig $config, DeskproContainer $container)
    {
        parent::__construct($config);
        $this->container = $container;

        $em = $container->getEm();
        $this->em = $em->create(
            DriverManager::getConnection(array(
                'dbname'   => $config->getDatabase(),
                'user'     => $config->getUser(),
                'password' => $config->getPassword(),
                'host'     => $config->getHost(),
                'driver'   => 'pdo_mysql',
            )),
            $em->getConfiguration()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getUsersCount($min_id = 0)
    {
        $query = 'SELECT count(id) FROM people WHERE id > :min_id ORDER BY id ASC';
        return $this->em->getConnection()->fetchColumn($query, array('min_id' => $min_id));
    }

    /**
     * {@inheritdoc}
     */
    public function getTicketsCount($min_id = 0)
    {
        $query = 'SELECT count(id) FROM tickets WHERE id > :min_id ORDER BY id ASC';
        return $this->em->getConnection()->fetchColumn($query, array('min_id' => $min_id));
    }

    /**
     * Returns a collection of people
     *
     * @param int $limit
     * @param int $min_id
     *
     * @return Entity\Person[]
     */
    public function findUsers($limit, $min_id = 0)
    {
        $criteria = new Criteria();
        $criteria
            ->where($criteria->expr()->gt('id', $min_id))
            ->setMaxResults($limit)
        ;

        return $this->findUsersByCriteria($criteria);
    }

    /**
     * Returns a collection of people by criteria
     *
     * @param Criteria $criteria
     * @return \Doctrine\Common\Collections\Collection
     */
    public function findUsersByCriteria(Criteria $criteria)
    {
        /** @var EntityRepository\Person $person_repository */
        $person_repository = $this->em->getRepository('DeskPRO:Person');

        return $person_repository->matching($criteria);
    }

    /**
     * Returns a collection of tickets
     *
     * @param int $limit
     * @param int $min_id
     *
     * @return Entity\Ticket[]
     */
    public function findTickets($limit, $min_id = 0)
    {
        $criteria = new Criteria();
        $criteria
            ->where($criteria->expr()->gt('id', $min_id))
            ->setMaxResults($limit)
        ;

        /** @var EntityRepository\Ticket $ticket_repository */
        $ticket_repository = $this->em->getRepository('DeskPRO:Ticket');

        return $ticket_repository->matching($criteria);
    }

    /**
     * @param Entity\Blob $blob
     * @return null|string
     */
    public function getBlobData(Entity\Blob $blob)
    {
        return $this->container->getBlobStorage()->copyBlobRecordToString($blob);
    }
}