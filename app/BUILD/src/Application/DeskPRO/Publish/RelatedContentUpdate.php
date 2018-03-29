<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Publish;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;

class RelatedContentUpdate
{
    /** @var DomainObject */
    protected $entity;
    /** @var string */
    protected $type;
    /** @var \Application\DeskPRO\DBAL\Connection */
    protected $db;

    public function __construct($entity)
    {
        $this->entity = $entity;
        $this->type   = $entity->getTableName();

        $this->db = App::getDb();
    }

    public function addRelated($type, $id)
    {
        $this->removeRelated($type, $id);
        $this->db->insert('related_content', [
            'object_type'     => $this->type,
            'object_id'       => $this->entity->id,
            'rel_object_type' => $type,
            'rel_object_id'   => $id,
        ]);
    }

    public function removeRelated($type, $id)
    {
        $params = [
            // For checking other object linked to this object
            $type,
            $id,
            $this->type,
            $this->entity->id,

            // For checking this object linked to other
            $this->type,
            $this->entity->id,
            $type,
            $id,
        ];

        $this->db->executeUpdate('
            DELETE FROM related_content
            WHERE
                (
                    object_type = ?
                    AND object_id = ?
                    AND rel_object_type = ?
                    AND rel_object_id = ?
                )
                OR
                (
                    object_type = ?
                    AND object_id = ?
                    AND rel_object_type = ?
                    AND rel_object_id = ?
                )
            LIMIT 1
        ', $params);
    }
}
