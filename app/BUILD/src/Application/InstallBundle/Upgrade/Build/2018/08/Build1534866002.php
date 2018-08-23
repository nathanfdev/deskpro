<?php

namespace Application\InstallBundle\Upgrade\Build;

/**
 * Class Build1534866002.
 */
class Build1534866002 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE phone_numbers ADD organization_id INT DEFAULT NULL, ADD type VARCHAR(255) NOT NULL');
        $this->execDbQuery('default', 'ALTER TABLE phone_numbers ADD CONSTRAINT FK_E7DC46CB32C8A3DE FOREIGN KEY (organization_id) REFERENCES organizations (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'CREATE INDEX IDX_E7DC46CB32C8A3DE ON phone_numbers (organization_id)');
    }

    public function run()
    {
    }
}
