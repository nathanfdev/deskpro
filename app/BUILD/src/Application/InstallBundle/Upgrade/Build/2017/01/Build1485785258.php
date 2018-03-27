<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1485785258 extends AbstractBuild
{
    public function run()
    {
        $this->out('Fix PK on products');
        $fk = $this->getSchemaHelper()->findForeignKey('products', 'parent_id', 'products', 'id');
        if ($fk) {
            $this->execDbQuery('default', 'ALTER TABLE products DROP FOREIGN KEY '.$fk->getName());
        }
        $this->execDbQuery('default', 'ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5A727ACA70 FOREIGN KEY (parent_id) REFERENCES products (id) ON DELETE SET NULL');
    }
}
