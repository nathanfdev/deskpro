<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1577707394 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE topic_subscriptions ADD root_category_brand_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE topic_subscriptions ADD CONSTRAINT FK_F34BD8DBD86F6514 FOREIGN KEY (root_category_brand_id) REFERENCES brands (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'CREATE INDEX IDX_F34BD8DBD86F6514 ON topic_subscriptions (root_category_brand_id)');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_subscriptions ADD root_category_brand_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE community_topic_subscriptions ADD CONSTRAINT FK_D2BCEED0D86F6514 FOREIGN KEY (root_category_brand_id) REFERENCES brands (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'CREATE INDEX IDX_D2BCEED0D86F6514 ON community_topic_subscriptions (root_category_brand_id)');
        $this->execDbQuery('default', 'ALTER TABLE news_subscriptions ADD root_category_brand_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE news_subscriptions ADD CONSTRAINT FK_5194E647D86F6514 FOREIGN KEY (root_category_brand_id) REFERENCES brands (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'CREATE INDEX IDX_5194E647D86F6514 ON news_subscriptions (root_category_brand_id)');
        $this->execDbQuery('default', 'ALTER TABLE kb_subscriptions ADD root_category_brand_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE kb_subscriptions ADD CONSTRAINT FK_1F05AAF5D86F6514 FOREIGN KEY (root_category_brand_id) REFERENCES brands (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'CREATE INDEX IDX_1F05AAF5D86F6514 ON kb_subscriptions (root_category_brand_id)');
        $this->execDbQuery('default', 'ALTER TABLE download_subscriptions ADD root_category_brand_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE download_subscriptions ADD CONSTRAINT FK_23B05F1DD86F6514 FOREIGN KEY (root_category_brand_id) REFERENCES brands (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'CREATE INDEX IDX_23B05F1DD86F6514 ON download_subscriptions (root_category_brand_id)');
    }

    public function run()
    {
        $connection   = $this->getDbConnection('default');
        $brands       = $connection->fetchAll('SELECT * FROM brands ORDER BY id ASC');
        $defaultBrand = current($brands);
        $tables       = ['kb_subscriptions', 'news_subscriptions', 'download_subscriptions', 'community_topic_subscriptions'];
        
        foreach ($tables as $table) {
            foreach ($brands as $brand) {
                if ($brand['id'] === $defaultBrand['id']) {
                    continue;
                }

                // Duplicate subscription for all brands so no subscriptions will be lost
                $this->execDbQuery('default', "
                    INSERT INTO {$table} (person_id, root_category, root_category_brand_id)
                    SELECT person_id, root_category, {$brand['id']}
                    FROM {$table}
                    WHERE person_id IS NOT NULL AND root_category = 1 AND root_category_brand_id IS NULL
                ");
            }

            // Fill default brand subscription
            $this->execDbQuery('default', "
                UPDATE {$table} set root_category_brand_id = {$defaultBrand['id']}
                WHERE person_id IS NOT NULL AND root_category = 1 AND root_category_brand_id IS NULL
            ");
        }
    }
}
