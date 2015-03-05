<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\InstallBundle\Upgrade\Build;

class Build1407410155 extends AbstractBuild
{
    public function run()
    {
        $this->out("Adding tables and fields for org and user files");
        $this->execMutateSql("CREATE TABLE organization_files (id INT AUTO_INCREMENT NOT NULL, organization_id INT DEFAULT NULL, agent_id INT DEFAULT NULL, blob_id INT DEFAULT NULL, date_created DATETIME NOT NULL, note VARCHAR(255) NOT NULL, INDEX IDX_88B2A69E32C8A3DE (organization_id), INDEX IDX_88B2A69E3414710B (agent_id), INDEX IDX_88B2A69EED3E8EA5 (blob_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        $this->execMutateSql("CREATE TABLE people_files (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, agent_id INT DEFAULT NULL, blob_id INT DEFAULT NULL, date_created DATETIME NOT NULL, note VARCHAR(255) NOT NULL, INDEX IDX_CD563A19217BBB47 (person_id), INDEX IDX_CD563A193414710B (agent_id), INDEX IDX_CD563A19ED3E8EA5 (blob_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        $this->execMutateSql("ALTER TABLE organization_files ADD CONSTRAINT FK_88B2A69E32C8A3DE FOREIGN KEY (organization_id) REFERENCES organizations (id) ON DELETE CASCADE");
        $this->execMutateSql("ALTER TABLE organization_files ADD CONSTRAINT FK_88B2A69E3414710B FOREIGN KEY (agent_id) REFERENCES people (id) ON DELETE SET NULL");
        $this->execMutateSql("ALTER TABLE organization_files ADD CONSTRAINT FK_88B2A69EED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id) ON DELETE SET NULL");
        $this->execMutateSql("ALTER TABLE people_files ADD CONSTRAINT FK_CD563A19217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE");
        $this->execMutateSql("ALTER TABLE people_files ADD CONSTRAINT FK_CD563A193414710B FOREIGN KEY (agent_id) REFERENCES people (id) ON DELETE SET NULL");
        $this->execMutateSql("ALTER TABLE people_files ADD CONSTRAINT FK_CD563A19ED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id) ON DELETE SET NULL");
    }
}
