<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1469786839 extends AbstractBuild
{
    public function run()
    {
        $this->out('Add tickets link to brand');

        // This might already be done because we back-ported this into a big alter as part of the new-agent package
        // in BuildNewAgent_0060_ticketalter1

        $sh = $this->getSchemaHelper();
        if (!$sh->tableHasColumn('tickets', 'brand_id')) {
            $instructions   = [];
            $instructions[] = 'ADD brand_id INT DEFAULT NULL';
            $instructions[] = 'ADD CONSTRAINT FK_54469DF444F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE SET NULL';
            $instructions[] = 'ADD INDEX IDX_54469DF444F5D008 (brand_id)';

            $this->execSlowAlterTable('tickets', implode(', ', $instructions));
        }
    }
}
