<?php

namespace Application\InstallBundle\Upgrade\Build;

use Doctrine\DBAL\Connection;

class Build1576586549 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        foreach ([
             ['custom_def_chat', 'custom_data_chat'],
             ['custom_def_people', 'custom_data_person'],
             ['custom_def_ticket', 'custom_data_ticket'],
             ['custom_def_article', 'custom_data_article'],
             ['custom_def_billing', 'custom_data_billing'],
             ['custom_def_download', 'custom_data_product'],
             ['custom_def_products', 'custom_data_download'],
             ['custom_def_organizations', 'custom_data_organizations'],
             ['custom_def_community_topic', 'custom_data_community_topic'],
        ] as $feat) {
            $fieldIds = $this->getDbConnection()->fetchAllCol("
                SELECT id
                FROM {$feat[0]}
                WHERE handler_class = 'Application\\\\DeskPRO\\\\CustomFields\\\\Handler\\\\File'
            ");

            if (empty($fieldIds)) {
                continue;
            }

            $lastId = 0;
            while ($recs = $this->getNextBatch($feat[1], $fieldIds, $lastId)) {
                $this->out("Verifying {$feat[1]} blobs (id>$lastId)");

                $blobIds  = array_values($recs);
                $fieldIds = array_keys($recs);
                $lastId   = end($fieldIds);

                $this->getDbConnection()->executeUpdate(
                    "UPDATE blobs SET is_temp = 0 WHERE id IN (?)",
                    [$blobIds],
                    [Connection::PARAM_INT_ARRAY]
                );
            }
        }
    }

    private function getNextBatch($tbl, array $fieldIds, $lastId = 0)
    {
        return $this->getDbConnection()->fetchAllKeyValue("
            SELECT id, value
            FROM {$tbl}
            WHERE field_id IN (?) AND id > ?
            ORDER BY id ASC
            LIMIT 1000
        ", [$fieldIds, $lastId], [Connection::PARAM_INT_ARRAY, \PDO::PARAM_INT]);
    }
}
