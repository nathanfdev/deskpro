<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1503398255 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
        $this->out('New app2_app_state_v2 table');
        $this->execDbQuery('default', 'CREATE TABLE app2_app_state_v2 (id INT AUTO_INCREMENT NOT NULL, app_instance_id INT DEFAULT NULL, person_id INT DEFAULT NULL, entity_id VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, value LONGTEXT NOT NULL, value_type VARCHAR(50) NOT NULL, perm_read VARCHAR(50) NOT NULL, perm_write VARCHAR(50) NOT NULL, is_backend_only TINYINT(1) NOT NULL, persistedAt DATETIME DEFAULT NULL, updatedAt DATETIME DEFAULT NULL, INDEX IDX_B90B064463B454A1 (app_instance_id), INDEX IDX_B90B0644217BBB47 (person_id), UNIQUE INDEX state_unique (app_instance_id, entity_id, name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE app2_app_state_v2 ADD CONSTRAINT FK_B90B064463B454A1 FOREIGN KEY (app_instance_id) REFERENCES app2_app_instance (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE app2_app_state_v2 ADD CONSTRAINT FK_B90B0644217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
