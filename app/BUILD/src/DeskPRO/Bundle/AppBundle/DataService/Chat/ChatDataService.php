<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataService\Chat;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * Class ChatDataService.
 */
class ChatDataService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * ChatDataService constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Gets a count to display to the user in portal.
     *
     * @param Person $person
     * @param string $type
     *
     * @return int
     */
    public function countUserChats(Person $person, $type)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select($qb->expr()->countDistinct('c.id'))
            ->from('DeskPRO:ChatConversation', 'c');

        $this->configureQbForQueryChats($qb, $person, $type);
        $qb->distinct(true);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Gets a pager of chats for a user in portal.
     *
     * @param Person $person
     * @param        $page
     * @param        $max_per_page
     * @param        $type
     *
     * @return Pagerfanta
     */
    public function getUserChatPager(Person $person, $page, $max_per_page, $type)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('c')
            ->from('DeskPRO:ChatConversation', 'c');

        $this->configureQbForQueryChats($qb, $person, $type);

        $qb->orderBy('c.id', 'DESC');

        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));
        $pager->setMaxPerPage($max_per_page);
        $pager->setCurrentPage($page);

        return $pager;
    }

    /**
     * @param Organization $organization
     *
     * @return int
     */
    public function getChatsCountForOrganization(Organization $organization)
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('COUNT(DISTINCT chat.id)')
            ->from('DeskPRO:ChatConversation', 'chat')
            ->innerJoin('chat.participants', 'participants')
            ->innerJoin('participants.organization', 'organization')
            ->andWhere('organization.id = :organization')
            ->setParameter('organization', $organization->getId());

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Make a query builder to select ChatConversation's for a user.
     *
     * @param QueryBuilder $qb
     * @param Person       $person
     * @param string       $type
     *
     * @throws \Exception
     *
     * @return QueryBuilder
     */
    private function configureQbForQueryChats(QueryBuilder $qb, Person $person, $type)
    {
        $qb->andWhere('c.is_agent = 0');
        $qb->andWhere('c.status = :status')->setParameter('status', ChatConversation::STATUS_ENDED);
        switch ($type) {
            case 'own':
                $qb->andWhere('c.person = :person')->setParameter('person', $person);
                break;
            case 'organization':
                $qb->innerJoin('c.person', 'person')
                    ->innerJoin('person.organization', 'organization')
                    ->andWhere('organization.id = :organization')
                    ->setParameter('organization', $person->getOrganizationId());
                break;
            default:
                throw new \Exception('Invalid type');
        }
    }
}
