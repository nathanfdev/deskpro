<?php

/**
 * DeskPRO.
 *
 * @category ORM
 */

namespace Application\DeskPRO\ORM\Util;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\SchemaHelper;
use DeskPRO\Component\Doctrine\ORM\Tools\SchemaTool as DPSchemaTool;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Simple utility methods for working with the ORM.
 */
class Util
{
    private function __construct()
    { /* Static class, no instances */
    }

    /**
     * Checks to see if $collection is a valid PersistentCollection, and if it's
     * been initialized yet.
     *
     * @param mixed $collection
     *
     * @return bool
     */
    public static function isCollectionInitialized($collection)
    {
        if ($collection instanceof PersistentCollection and $collection->isInitialized()) {
            return true;
        }

        return false;
    }

    /**
     * @param \Doctrine\ORM\EntityManager $em
     *
     * @return array
     */
    public static function getUpdateSchemaSql(EntityManager $em = null)
    {
        if ($em === null) {
            $em = App::getOrm();
        }

        $metadata = $em->getMetadataFactory()->getAllMetadata();
        $tool     = new SchemaTool($em);

        $arr   = $tool->getUpdateSchemaSql($metadata, true);
        $lines = [];
        foreach ($arr as $a) {
            // Doctrine doesn't seem to detect this properly and always thinks this is needed
            if ($a != 'ALTER TABLE email_uids CHANGE id id VARCHAR(100) NOT NULL') {
                if (strpos($a, 'CREATE TABLE') !== false) {
                    $a .= ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci';
                }

                $lines[] = $a;
            }
        }

        return $lines;
    }

    /**
     * Check that all FK constraints exist in DB.
     *
     * @param EntityManager $em
     *
     * @return bool
     */
    public static function isAllFKConstraintsExist(EntityManager $em)
    {
        $metadata     = $em->getMetadataFactory()->getAllMetadata();
        $schemaTool   = new DPSchemaTool($em);
        $schemaHelper = new SchemaHelper($em->getConnection());
        $schema       = $schemaTool->getSchemaFromMetadata($metadata);

        foreach ($schema->getTables() as $table) {
            foreach ($table->getForeignKeys() as $foreignKey) {
                try {
                    $realKey = $schemaHelper->findForeignKey(
                        $table->getName(),
                        $foreignKey->getColumns(),
                        $foreignKey->getForeignTableName(),
                        $foreignKey->getForeignColumns()
                    );
                    if (!$realKey) {
                        return false;
                    }
                } catch (\Exception $e) {
                    return false;
                }
            }
        }

        return true;
    }
}
