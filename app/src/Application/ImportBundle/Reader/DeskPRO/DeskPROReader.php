<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\ImportBundle\Reader\DeskPRO;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity;
use Application\DeskPRO\EntityRepository;
use Application\ImportBundle\Reader\AbstractReader;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;

/**
 * Class DeskPROReader.
 *
 * @property DeskPROConfig $config
 */
class DeskPROReader extends AbstractReader implements DeskPROReaderInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var DeskproBlobStorage
     */
    protected $blob_storage;

    /**
     * Constructor.
     *
     * @param DeskPROConfig      $config
     * @param EntityManager      $em
     * @param DeskproBlobStorage $blob_storage
     */
    public function __construct(DeskPROConfig $config, EntityManager $em, DeskproBlobStorage $blob_storage)
    {
        parent::__construct($config);

        $this->em           = $em;
        $this->blob_storage = $blob_storage;
    }

    /**
     * {@inheritdoc}
     */
    public function checkConfig()
    {
        return $this->em->getConnection()->connect();
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
        $criteria = new Criteria();
        $criteria
            ->where($criteria->expr()->gt('id', $min_id))
            ->setMaxResults($limit)
        ;

        return $this->findUsersByCriteria($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function findUsersByCriteria(Criteria $criteria)
    {
        /** @var EntityRepository\Person $person_repository */
        $person_repository = $this->em->getRepository('DeskPRO:Person');

        return $person_repository->matching($criteria);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function findCustomDefTickets()
    {
        return $this->em->getRepository('DeskPRO:CustomDefTicket')->findBy(array('parent' => null));
    }

    /**
     * {@inheritdoc}
     */
    public function findCustomDefPeople()
    {
        return $this->em->getRepository('DeskPRO:CustomDefPerson')->findBy(array('parent' => null));
    }

    /**
     * {@inheritdoc}
     */
    public function findCustomDefOrganizations()
    {
        return $this->em->getRepository('DeskPRO:CustomDefOrganization')->findBy(array('parent' => null));
    }

    /**
     * {@inheritdoc}
     */
    public function findCustomDefArticles()
    {
        return $this->em->getRepository('DeskPRO:CustomDefArticle')->findBy(array('parent' => null));
    }

    /**
     * {@inheritdoc}
     */
    public function findCustomDefFeedback()
    {
        return $this->em->getRepository('DeskPRO:CustomDefFeedback')->findBy(array('parent' => null));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlobData(Entity\Blob $blob)
    {
        return $this->blob_storage->copyBlobRecordToString($blob);
    }
}
