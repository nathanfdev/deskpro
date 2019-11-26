<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class TicketFilterRepository extends EntityRepository
{
    public function getFilters()
    {
        return $this->findAll();
    }

    /**
     * @param string $label
     *
     * @return \DeskPRO\Bundle\AppBundle\Entity\TicketFilter[]
     */
    public function getFiltersByLabel($label)
    {
        return $this->_em
            ->createQuery('
                SELECT tf
                FROM DeskPRO:LegacyTicketFilter tf
                WHERE tf.terms LIKE :term_type
                    AND tf.terms LIKE :label
            ')
            ->setParameters([
                'term_type' => '%"type":"label"%',
                'label'     => "%\"{$label}\"%",
            ])
            ->execute();
    }
}
