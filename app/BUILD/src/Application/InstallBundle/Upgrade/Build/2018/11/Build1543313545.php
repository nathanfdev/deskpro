<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1543313545 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE brands ADD slug VARCHAR(255) NOT NULL');
    }

    public function run()
    {
        $brands = $this->getDbConnection('default')->fetchAll('SELECT id, name FROM `brands`');
        foreach ($brands as $brand) {
            $statement = $this->getDbConnection('default')->prepare('UPDATE `brands` SET slug = :slug WHERE `id` = :id');
            $statement->execute([
                'id'   => $brand['id'],
                'slug' => 'brand-'.$brand['id'],
            ]);
        }

        // add unique index after filling the slug column
        // to prevent unique errors because the slug column can't be null
        $this->execDbQuery('default', 'CREATE UNIQUE INDEX UNIQ_7EA24434989D9B62 ON brands (slug)');
    }
}
