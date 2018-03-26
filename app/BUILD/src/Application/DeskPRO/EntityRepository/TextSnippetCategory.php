<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\Person as PersonEntity;

class TextSnippetCategory extends AbstractEntityRepository
{
    public function getCatsForAgent($typename, PersonEntity $agent)
    {
        $agent->loadHelper('AgentTeam');

        $dql = '
            SELECT c
            FROM DeskPRO:TextSnippetCategory c
            WHERE
                c.typename = ?1
                AND (c.person = ?2 OR c.is_global = true)
        ';

        $coll = $this->getEntityManager()->createQuery($dql)
            ->setParameter(1, $typename)
            ->setParameter(2, $agent)
            ->execute();

        return $coll;
    }

    public function getAllByType($typename)
    {
        return $this->getEntityManager()->createQuery('
            SELECT c
            FROM DeskPRO:TextSnippetCategory c INDEX BY c.id
            WHERE c.typename = ?0

        ')->execute([$typename]);
    }
}
