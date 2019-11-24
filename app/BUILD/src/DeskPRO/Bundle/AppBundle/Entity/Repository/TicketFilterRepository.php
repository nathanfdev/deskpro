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
            ->createQuery("
                SELECT tf
                FROM DeskPRO:LegacyTicketFilter tf
                WHERE JSON_CONTAINS(JSON_EXTRACT(tf.terms, '$[*].type'), :term_type) = 1
                    AND JSON_CONTAINS(JSON_EXTRACT(tf.terms, '$[*].options.label'), :label) = 1
            ")
            ->setParameters([
                'term_type' => '"label"',
                'label'     => "\"{$label}\"",
            ])
            ->execute();
    }
}
