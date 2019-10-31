<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

class CustomDefPerson extends CustomDefAbstract
{
    public function getEnabledPublicUserFields()
    {
        $q = $this->_em->createQuery("
            SELECT f
            FROM {$this->_entityName} f INDEX BY f.id
            WHERE f.is_enabled = true AND f.is_public = true AND f.is_agent_field = false
            ORDER BY f.display_order ASC, f.title
        ");

        $res = $q->execute();
        if (count($res)) {
            $this->preloadHierarchy();
        }

        return $res;
    }
}
