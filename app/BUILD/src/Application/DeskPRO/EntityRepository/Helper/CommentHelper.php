<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository\Helper;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\EntityRepository\AbstractEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Orb\Util\Arrays;
use Orb\Util\Numbers;

class CommentHelper
{
    /**
     * @var \Application\DeskPRO\EntityRepository\AbstractEntityRepository
     */
    protected $repos;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Doctrine\ORM\Mapping\ClassMetadata
     */
    protected $class;

    /**
     * @var string
     */
    protected $entity_name;

    /**
     * @var string
     */
    protected $table_name;

    /**
     * @var string
     */
    protected $comment_entity_name;

    /**
     * @var string
     */
    protected $comment_table_name;

    /**
     * @var string
     */
    protected $comment_join_field;

    public function __construct(
        EntityManager $em,
        AbstractEntityRepository $repos,
        $entity_name,
        ClassMetadata $class,
        $comment_entity_name,
        $comment_table_name,
        $comment_join_field
    ) {
        $this->repos       = $repos;
        $this->em          = $em;
        $this->class       = $class;
        $this->entity_name = $entity_name;
        $this->table_name  = $class->getTableName();

        $this->comment_entity_name = $comment_entity_name;
        $this->comment_table_name  = $comment_table_name;
        $this->comment_join_field  = $comment_join_field;
    }

    /**
     * Count the number of comments on a record.
     *
     * @param $record
     * @param bool $user_visible
     *
     * @return int
     */
    public function countOn($record, $user_visible = true)
    {
        if ($user_visible) {
            $user_visible = " AND status = 'visible'";
        } else {
            $user_visible = '';
        }

        $sql = "
            SELECT COUNT(*)
            FROM {$this->comment_table_name}
            WHERE
                {$this->comment_join_field} = ?
                $user_visible
        ";

        return $this->em->getConnection()->fetchColumn($sql, [$record->getId()]);
    }

    /**
     * Count the number of comments on a number of records.
     *
     * @param array $records
     * @param bool  $user_visible
     *
     * @return array
     */
    public function countsOnCollection(array $records, $user_visible = true)
    {
        if ($user_visible) {
            $user_visible = " AND status = 'visible'";
        } else {
            $user_visible = '';
        }

        $ids = [];

        foreach ($records as $r) {
            // Ids provided
            if (Numbers::isInteger($r)) {
                $ids[] = $r;

            // Objects provided
            } else {
                $ids[] = $r->getId();
            }
        }

        $ids = Arrays::removeFalsey($ids);
        $ids = array_unique($ids);

        if (!$ids) {
            return [];
        }

        /** @var Connection $conn */
        $conn = $this->em->getConnection();
        $sql  = "
            SELECT {$conn->quoteIdentifier($this->comment_join_field)}, COUNT(*)
            FROM {$conn->quoteIdentifier($this->comment_table_name)}
            WHERE
                {$conn->quoteIdentifier($this->comment_join_field)} IN (?)
                $user_visible
            GROUP BY {$conn->quoteIdentifier($this->comment_join_field)}
        ";

        $counts = $conn->fetchAllKeyValue($sql, [$ids], [Connection::PARAM_INT_ARRAY]);

        return $counts;
    }
}
