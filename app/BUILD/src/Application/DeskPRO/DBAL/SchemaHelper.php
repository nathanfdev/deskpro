<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\DBAL;

use Doctrine\DBAL;
use Orb\Util\Arrays;

class SchemaHelper
{
    /**
     * @var DBAL\Connection
     */
    private $db;

    /**
     * @var DBAL\Schema\AbstractSchemaManager
     */
    private $schema;

    /**
     * @param DBAL\Connection $db
     */
    public function __construct(DBAL\Connection $db)
    {
        $this->db = $db;
    }

    /**
     * @return DBAL\Schema\AbstractSchemaManager
     */
    public function getSchemaManager()
    {
        if ($this->schema) {
            return $this->schema;
        }

        $this->schema = $this->db->getSchemaManager();

        return $this->schema;
    }

    /**
     * @param string       $table  The table that has the FK
     * @param string|array $col    The column on the table that has the FK
     * @param string       $fTable The foreign table
     * @param string|array $fCol   The column in the foreign table
     *
     * @return \Doctrine\DBAL\Schema\ForeignKeyConstraint|null
     */
    public function findForeignKey($table, $col, $fTable, $fCol)
    {
        if (!is_array($col)) {
            $col = [$col];
        }
        if (!is_array($fCol)) {
            $fCol = [$fCol];
        }

        foreach ($this->getSchemaManager()->listTableForeignKeys($table) as $fk) {
            if (
                $fk->getForeignTableName() === $fTable
                && count($col) === count($fk->getLocalColumns()) && Arrays::isIn($col, $fk->getLocalColumns(), true, true)
                && count($fCol) === count($fk->getForeignColumns()) && Arrays::isIn($fCol, $fk->getForeignColumns(), true, true)
            ) {
                return $fk;
            }
        }

        return;
    }

    /**
     * @param string $table   The table to check
     * @param string $findCol The column to check for
     *
     * @return bool
     */
    public function tableHasColumn($table, $findCol)
    {
        $cols = array_filter($this->getSchemaManager()->listTableColumns($table), function (\Doctrine\DBAL\Schema\Column $c) use ($findCol) {
            return $c->getName() === $findCol;
        });

        return count($cols) > 0;
    }

    /**
     * @param string          $table The table that has the index
     * @param string|string[] $cols  The columns the index is on
     *
     * @return DBAL\Schema\Index|null
     */
    public function findIndex($table, $cols)
    {
        if (!is_array($cols)) {
            $cols = [$cols];
        }

        foreach ($this->getSchemaManager()->listTableIndexes($table) as $idx) {
            $idx_cols = $idx->getUnquotedColumns();
            if (count($cols) === count($idx_cols) && Arrays::isIn($cols, $idx_cols, true, true)) {
                return $idx;
            }
        }

        return;
    }

    /**
     * This will rename columns with recreating all FKs.
     *
     * @param string $table
     * @param string $oldColName
     * @param string $newColName
     */
    public function renameColumn($tableName, $oldColName, $newColName)
    {
        $fks        = $this->getSchemaManager()->listTableForeignKeys($tableName);
        $table      = $this->getSchemaManager()->listTableDetails($tableName);
        $col        = $table->getColumn($oldColName);
        $droppedFKs = [];

        foreach ($fks as $fk) {
            if (!in_array($oldColName, $fk->getColumns())) {
                continue;
            }
            $droppedFKs[] = $fk;
            $this->db->executeQuery(sprintf('ALTER TABLE `%s` DROP FOREIGN KEY `%s`', $tableName, $fk->getName()));
        }
        $this->db->executeQuery(sprintf('ALTER TABLE `%s` CHANGE COLUMN `%s` `%s` %s', $tableName, $oldColName,
            $newColName, $col->getType()->getSQLDeclaration($col->toArray(), $this->db->getDatabasePlatform())));

        foreach ($droppedFKs as $fk) {
            $addFKQuery = sprintf(
                'ALTER TABLE `%s` ADD CONSTRAINT `%s` FOREIGN KEY (`%s`) REFERENCES `%s` (`%s`)',
                $tableName,
                $fk->getName(),
                implode(',', array_map(function ($column) use ($oldColName, $newColName) {
                    return $column === $oldColName ? $newColName : $column;
                }, $fk->getColumns())),
                $fk->getForeignTableName(),
                implode(',', $fk->getForeignColumns())
            );
            if ($fk->onDelete()) {
                $addFKQuery .= ' ON DELETE '.$fk->onDelete();
            }
            if ($fk->onUpdate()) {
                $addFKQuery .= ' ON UPDATE '.$fk->onUpdate();
            }
            $this->db->executeQuery($addFKQuery);
        }
    }
}
