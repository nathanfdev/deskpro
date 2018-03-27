<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\Person as PersonEntity;

class AbstractRevisionRepository extends AbstractEntityRepository
{
    public function getRevisionsForAgent(PersonEntity $agent, array $options = [])
    {
        $class_parts = explode('\\', get_class($this));
        $class_name  = array_pop($class_parts);

        if (isset($options['date_range'])) {
            $query = $this->_em->createQuery("
                SELECT rev
                FROM DeskPRO:{$class_name} rev INDEX BY rev.id
                WHERE rev.person = ?1
                AND rev.date_created BETWEEN ?2 AND ?3
                ORDER BY rev.date_created ASC
            ")
                ->setParameter(1, $agent)
                ->setParameter(2, $options['date_range']['start'])
                ->setParameter(3, $options['date_range']['end'])
            ;
        } else {
            $query = $this->_em->createQuery("
                SELECT rev
                FROM DeskPRO:{$class_name} rev INDEX BY log.id
                WHERE rev.person = ?1
                ORDER BY rev.date_created ASC
            ")
                ->setParameter(1, $agent);
        }

        return $query->execute();
    }
}
