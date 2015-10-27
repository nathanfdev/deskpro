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

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat as AgentChatEntity;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;

/**
 * Class AgentChatMessage.
 */
class AgentChatMessage extends EntityRepository
{
    /**
     * @param AgentChatEntity $chat
     * @param $searchString
     *
     * @return array
     */
    public function searchString(AgentChatEntity $chat, $searchString)
    {
        $qb = $this->createQueryBuilder('acm');
        $qb->where('acm.chat = :chat')
            ->setParameter('chat', $chat);
        if ($searchString) {
            $qb->andWhere('acm.message LIKE :message')
                ->setParameter('message', '%'.$searchString.'%');
        }
        $results = $qb->getQuery()->getResult(AbstractQuery::HYDRATE_SCALAR);
        //TODO note that this can be bottle neck, but its so cute =)
        $ids = array_map('current', $results);

        return $ids;
    }

    /**
     * @param Person $user
     * @param $chats
     *
     * @return array
     */
    public function countMessages(Person $user, $chats)
    {
        $qb = $this->createQueryBuilder('acm');
        $qb->select('IDENTITY(acm.chat) as chat_id, COUNT(acm.id) as cnt')
            ->where('acm.chat IN (:chats)')
            ->andWhere('acm.status < :status')
            ->andWhere('acm.person != :person')
            ->groupBy('acm.chat')
            ->setParameter('chats', $chats)
            ->setParameter('status', 1)
            ->setParameter('person', $user);

        return $qb->getQuery()->getResult(AbstractQuery::HYDRATE_SCALAR);
    }
}
