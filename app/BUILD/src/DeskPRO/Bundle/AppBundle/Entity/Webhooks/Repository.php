<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Webhooks;

use Doctrine\ORM\EntityRepository;

class Repository extends EntityRepository
{
    /**
     * @param $authId
     *
     * @return null|TicketWebhook
     */
    public function findOneByAuthId($authId)
    {
        return $this->findOneBy(['authId' => $authId]);
    }
}
