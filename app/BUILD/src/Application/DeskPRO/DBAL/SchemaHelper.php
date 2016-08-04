<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
     * @param string $table   The table that has the FK
     * @param string $col     The column on the table that has the FK
     * @param string $f_table The foreign table
     * @param string $f_col   The column in the foreign table
     *
     * @return \Doctrine\DBAL\Schema\ForeignKeyConstraint|null
     */
    public function findForeignKey($table, $col, $f_table, $f_col)
    {
        foreach ($this->getSchemaManager()->listTableForeignKeys($table) as $fk) {
            if (
                $fk->getForeignTableName() == $f_table
                && in_array($col, $fk->getLocalColumns())
                && in_array($f_col, $fk->getForeignColumns())
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
            $cols = array($cols);
        }

        foreach ($this->getSchemaManager()->listTableIndexes($table) as $idx) {
            $idx_cols = $idx->getUnquotedColumns();
            if (count($cols) === count($idx_cols) && Arrays::isIn($cols, $idx_cols, true, true)) {
                return $idx;
            }
        }

        return;
    }
}
