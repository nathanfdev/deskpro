<?php

namespace DeskPRO\Component\Doctrine\ORM\Tools;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\TableDiff;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool as BaseSchemaTool;

class SchemaTool extends BaseSchemaTool
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * {@inheritdoc}
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        parent::__construct($em);
    }

    /**
     * @return \Doctrine\DBAL\Platforms\AbstractPlatform
     */
    public function getPlatform()
    {
        return $this->em->getConnection()->getDatabasePlatform();
    }

    /**
     * @param array $classes The classes to consider
     *
     * @return \Doctrine\DBAL\Schema\SchemaDiff
     */
    public function getSchemaDiff(array $classes)
    {
        $sm = $this->em->getConnection()->getSchemaManager();

        $fromSchema = $sm->createSchema();
        $toSchema   = $this->getSchemaFromMetadata($classes);

        $comparator = new Comparator();
        $schemaDiff = $comparator->compare($fromSchema, $toSchema);

        return $schemaDiff;
    }

    /**
     * Table alters are split between alters that can happen online and those that cant.
     *
     * A safe online alter is:
     * - adding a new nullable column, or a column with a default value
     * - adding a new unique index to a newly created column
     * - adding a new non-unique index to any column
     * - adding a new FK to a new column
     *
     * Everything else, this tool cant guarantee it is safe to do online. For * example, removing an index isnt safe
     * because removing the index might cause bad performance problems without * other code changes.
     * The developer might manually move queires around though.
     *
     * @param TableDiff $tableDiff
     *
     * @return bool
     */
    public function isTableDiffBackwardsCompatible(TableDiff $tableDiff)
    {
        $newColumnNames = [];

        if (!empty($tableDiff->addedColumns)) {
            foreach ($tableDiff->addedColumns as $column) {
                // must have a default value to be bc
                if ($column->getNotnull() && $column->getDefault() === null) {
                    return false;
                }

                $newColumnNames[] = $column->getName();
            }
        }

        if (!empty($tableDiff->addedIndexes)) {
            foreach ($tableDiff->addedIndexes as $index) {
                if ($index->isUnique()) {
                    // not bc if its not on a newly created col
                    foreach ($index->getColumns() as $c) {
                        if (!in_array($c, $newColumnNames)) {
                            return false;
                        }
                    }
                }
            }
        }

        if (!empty($tableDiff->addedForeignKeys)) {
            foreach ($tableDiff->addedForeignKeys as $fk) {
                // not bc if its not on a newly created col
                foreach ($fk->getColumns() as $c) {
                    if (!in_array($c, $newColumnNames)) {
                        return false;
                    }
                }
            }
        }

        if (
            $tableDiff->getNewName()
            || !empty($tableDiff->changedColumns)
            || !empty($tableDiff->changedForeignKeys)
            || !empty($tableDiff->changedIndexes)
            || !empty($tableDiff->removedColumns)
            || !empty($tableDiff->removedForeignKeys)
            || !empty($tableDiff->removedIndexes)
        ) {
            return false;
        }

        return true;
    }
}
