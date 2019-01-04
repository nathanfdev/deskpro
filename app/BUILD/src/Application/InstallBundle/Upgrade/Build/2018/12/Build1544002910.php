<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1544002910 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE feedback_status_categories ADD brand_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE feedback_status_categories ADD CONSTRAINT FK_F8E804FA44F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE INDEX IDX_F8E804FA44F5D008 ON feedback_status_categories (brand_id)');
        $this->execDbQuery('default', 'ALTER TABLE feedback_categories ADD brand_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE feedback_categories ADD CONSTRAINT FK_66FE683244F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE INDEX IDX_66FE683244F5D008 ON feedback_categories (brand_id)');
        $this->execDbQuery('default', 'ALTER TABLE feedback ADD brand_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE feedback ADD CONSTRAINT FK_D229445844F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE INDEX IDX_D229445844F5D008 ON feedback (brand_id)');
        $this->execDbQuery('default', 'ALTER TABLE custom_def_feedback ADD brand_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE custom_def_feedback ADD CONSTRAINT FK_CC9CDDD844F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE SET NULL');
        $this->execDbQuery('default', 'CREATE INDEX IDX_CC9CDDD844F5D008 ON custom_def_feedback (brand_id)');
    }

    public function run()
    {
        $brands = $this->getDbConnection('default')->fetchAll('SELECT * FROM `brands`');
        $brand  = current($brands);

        $db = $this->getDbConnection('default');
        $db->executeUpdate('UPDATE `feedback` SET brand_id = :brand_id', ['brand_id' => $brand['id']]);
        $db->executeUpdate('UPDATE `feedback_categories` SET brand_id = :brand_id', ['brand_id' => $brand['id']]);
        $db->executeUpdate('UPDATE `feedback_status_categories` SET brand_id = :brand_id', ['brand_id' => $brand['id']]);
        $db->executeUpdate('UPDATE `custom_def_feedback` SET brand_id = :brand_id', ['brand_id' => $brand['id']]);
    }
}
