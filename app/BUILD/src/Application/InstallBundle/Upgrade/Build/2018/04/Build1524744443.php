<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1524744443 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE person_to_brand (person_id INT NOT NULL, brand_id INT NOT NULL, INDEX IDX_83286D5F217BBB47 (person_id), INDEX IDX_83286D5F44F5D008 (brand_id), PRIMARY KEY(person_id, brand_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE person_to_brand ADD CONSTRAINT FK_83286D5F217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE person_to_brand ADD CONSTRAINT FK_83286D5F44F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $this->out('Bind persons to default brand');

        $brand          = null;
        $defaultBrandId = $this->readSetting('portal.default_brand');
        if ($defaultBrandId) {
            $brands = $this->getDbConnection('default')->fetchAll('SELECT id FROM `brands` WHERE id = ?', [$defaultBrandId]);
            $brand  = current($brands);
        }

        if (!$brand) {
            $brands = $this->getDbConnection('default')->fetchAll('SELECT id FROM `brands` ORDER BY id ASC limit 1');
            $brand  = current($brands);
        }

        $statement = $this->getDbConnection('default')->prepare('
            INSERT INTO `person_to_brand` (`person_id`, `brand_id`)
            SELECT id, :brand
            FROM people
        ');
        $statement->execute([
            'brand' => $brand['id'],
        ]);
    }
}
