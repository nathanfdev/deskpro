<?php

namespace Application\DeskPRO\EntityRepository\Traits;

use Application\DeskPRO\Entity\ContentAbstract;

/**
 * Trait ClearSlugHistoryTrait.
 */
trait ClearSlugHistoryTrait
{
    /**
     * @param \Application\DeskPRO\Entity\ContentAbstract $entity
     *
     * @return int
     */
    public function clearHistoryByEntity($entity)
    {
        if (!$entity instanceof ContentAbstract) {
            throw new \InvalidArgumentException('Entity should extends from ContentAbstract');
        }

        return $this->_em->getConnection()->executeUpdate("
            DELETE
            FROM {$this->getTableName()}
            WHERE {$this->getEntityFieldName()} = :entity_id
        ",
            ['entity_id' => $entity->getId()],
            ['entity_id' => \PDO::PARAM_INT]
        );
    }

    /**
     * @return string
     */
    abstract protected function getEntityFieldName();
}
