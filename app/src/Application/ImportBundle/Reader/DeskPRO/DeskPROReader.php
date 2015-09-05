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
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\ORM\EntityManager;
use Doctrine\DBAL\DriverManager;
use Application\ImportBundle\Reader\BaseReader;

/**
 * Class DeskPROReader
 * @package Application\ImportBundle\Reader\DeskPRO
 */
class DeskPROReader extends BaseReader
{
    /**
     * @var DeskproContainer
     */
    protected $c;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Constructor
     *
     * @param Config          $config
     * @param DeskproContainer $container
     *
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\ORMException
     */
    public function __construct(Config $config, DeskproContainer $container)
    {
        parent::__construct($config);
        $this->c = $container;

        $em = $container->getEm();
        $this->em = $em->create(
            DriverManager::getConnection(array(
                'dbname' => $config->getDatabase(),
                'user' => $config->getUser(),
                'password' => $config->getPassword(),
                'host' => $config->getHost(),
                'driver' => 'pdo_mysql',
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
     * {@inheritdoc}
     */
    public function findUsers($limit, $min_id = 0)
    {
        $criteria = new \Doctrine\Common\Collections\Criteria();
        $criteria
            ->where($criteria->expr()->gt('id', $min_id))
            ->setMaxResults($limit)
        ;
        return $this->em->getRepository('DeskPRO:Person')->matching($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function findTickets($limit, $min_id = 0)
    {
        $criteria = new \Doctrine\Common\Collections\Criteria();
        $criteria
            ->where($criteria->expr()->gt('id', $min_id))
            ->setMaxResults($limit)
        ;
        return $this->em->getRepository('DeskPRO:Ticket')->matching($criteria);
    }

    /**
     * @param Blob $blob
     * @return null|string
     */
    public function getBlobData(Blob $blob)
    {
        return $this->c->getBlobStorage()->copyBlobRecordToString($blob);
    }

    /**
     * @return bool
     * @throws OsTicketReaderException
     */
    public function isReady()
    {
        return null !== $this->getUsersCount();
    }
}