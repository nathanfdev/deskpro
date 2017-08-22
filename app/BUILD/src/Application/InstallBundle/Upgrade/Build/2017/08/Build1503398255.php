<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
