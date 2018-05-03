<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1469786840 extends AbstractBuild
{
    public function run()
    {
        $brands = $this->getDbConnection('default')->fetchAll('SELECT * FROM `brands`');
        $brand  = current($brands);

        $this->out('Bind existing tickets to brand');
        $this->execDbQuery('default', sprintf('UPDATE tickets SET brand_id = %d WHERE brand_id IS NULL', $brand['id']));
    }
}
