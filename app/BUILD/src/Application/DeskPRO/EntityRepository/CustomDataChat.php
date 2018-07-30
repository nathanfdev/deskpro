<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity;

class CustomDataChat extends AbstractEntityRepository
{
    public function getDataForTicket(Entity\ChatConversation $chat)
    {
        return $this->_em->createQuery('
            SELECT d
            FROM DeskPRO:CustomDataChat d INDEX BY d.field_id
            WHERE d.ticket = ?1
        ')->setParameter(1, $chat)->execute();
    }
}
