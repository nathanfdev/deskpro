<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

class Build1423725055 extends AbstractBuild
{
    public function run()
    {
        $this->out("Upgrade Ticket Messages Class");
		$this->execMutateSql("CREATE TABLE tickets_message_email_id (id INT AUTO_INCREMENT NOT NULL, message_id INT DEFAULT NULL, email_id VARCHAR(255) NOT NULL, INDEX IDX_C0D32802537A1329 (message_id), INDEX email_id_idx (email_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 ENGINE = InnoDB DEFAULT CHARSET=utf8");
		$this->execMutateSql("ALTER TABLE tickets_message_email_id ADD CONSTRAINT FK_C0D32802537A1329 FOREIGN KEY (message_id) REFERENCES tickets_messages (id) ON DELETE CASCADE");
    }
}