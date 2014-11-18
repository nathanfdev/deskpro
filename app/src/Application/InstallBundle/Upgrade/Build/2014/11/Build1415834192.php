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

class Build1415834192 extends AbstractBuild
{
    public function run()
    {
        $this->out("Change visitor ID to a string");

        $this->execMutateSql("ALTER TABLE article_comments DROP FOREIGN KEY FK_A76624170BEE6D");
        $this->execMutateSql("ALTER TABLE chat_blocks DROP FOREIGN KEY FK_A931A25970BEE6D");
        $this->execMutateSql("ALTER TABLE chat_conversations DROP FOREIGN KEY FK_5813432E70BEE6D");
        $this->execMutateSql("ALTER TABLE download_comments DROP FOREIGN KEY FK_B43CDE1470BEE6D");
        $this->execMutateSql("ALTER TABLE feedback_comments DROP FOREIGN KEY FK_10D03D5870BEE6D");
        $this->execMutateSql("ALTER TABLE news_comments DROP FOREIGN KEY FK_16A0357B70BEE6D");
        $this->execMutateSql("ALTER TABLE pretickets_content DROP FOREIGN KEY FK_E1110A2570BEE6D");
        $this->execMutateSql("ALTER TABLE ratings DROP FOREIGN KEY FK_CEB607C970BEE6D");
        $this->execMutateSql("ALTER TABLE searchlog  DROP FOREIGN KEY FK_8C79CD5C70BEE6D");
        $this->execMutateSql("ALTER TABLE sessions DROP FOREIGN KEY FK_9A609D1370BEE6D");
        $this->execMutateSql("ALTER TABLE tickets_messages DROP FOREIGN KEY FK_3A9962E270BEE6D");
        $this->execMutateSql("ALTER TABLE visitor_tracks DROP FOREIGN KEY FK_E002459270BEE6D");

        $this->execMutateSql("ALTER TABLE article_comments CHANGE visitor_id visitor_id VARCHAR(255) DEFAULT NULL");
        $this->execMutateSql("ALTER TABLE chat_blocks CHANGE visitor_id visitor_id VARCHAR(255) DEFAULT NULL");
        $this->execMutateSql("ALTER TABLE chat_conversations CHANGE visitor_id visitor_id VARCHAR(255) DEFAULT NULL");
        $this->execMutateSql("ALTER TABLE download_comments CHANGE visitor_id visitor_id VARCHAR(255) DEFAULT NULL");
        $this->execMutateSql("ALTER TABLE feedback_comments CHANGE visitor_id visitor_id VARCHAR(255) DEFAULT NULL");
        $this->execMutateSql("ALTER TABLE news_comments CHANGE visitor_id visitor_id VARCHAR(255) DEFAULT NULL");
        $this->execMutateSql("ALTER TABLE pretickets_content CHANGE visitor_id visitor_id VARCHAR(255) DEFAULT NULL");
        $this->execMutateSql("ALTER TABLE ratings CHANGE visitor_id visitor_id VARCHAR(255) DEFAULT NULL");
        $this->execMutateSql("ALTER TABLE searchlog CHANGE visitor_id visitor_id VARCHAR(255) DEFAULT NULL");
        $this->execMutateSql("ALTER TABLE sessions CHANGE visitor_id visitor_id VARCHAR(255) DEFAULT NULL");
        $this->execMutateSql("ALTER TABLE tickets_messages CHANGE visitor_id visitor_id VARCHAR(255) DEFAULT NULL");
        $this->execMutateSql("ALTER TABLE visitor_tracks CHANGE visitor_id visitor_id VARCHAR(255) DEFAULT NULL");

        $this->execMutateSql("ALTER TABLE visitors CHANGE id id VARCHAR(255) NOT NULL");

        $this->execMutateSql(
            "ALTER TABLE article_comments ADD CONSTRAINT FK_A76624170BEE6D FOREIGN KEY (visitor_id) REFERENCES visitors (id) ON DELETE SET NULL"
        );
        $this->execMutateSql(
            "ALTER TABLE chat_blocks ADD CONSTRAINT FK_A931A25970BEE6D FOREIGN KEY (visitor_id) REFERENCES visitors (id) ON DELETE SET NULL"
        );
        $this->execMutateSql(
            "ALTER TABLE chat_conversations ADD CONSTRAINT FK_5813432E70BEE6D FOREIGN KEY (visitor_id) REFERENCES visitors (id) ON DELETE SET NULL"
        );
        $this->execMutateSql(
            "ALTER TABLE download_comments ADD CONSTRAINT FK_B43CDE1470BEE6D FOREIGN KEY (visitor_id) REFERENCES visitors (id) ON DELETE SET NULL"
        );
        $this->execMutateSql(
            "ALTER TABLE feedback_comments ADD CONSTRAINT FK_10D03D5870BEE6D FOREIGN KEY (visitor_id) REFERENCES visitors (id) ON DELETE SET NULL"
        );
        $this->execMutateSql(
            "ALTER TABLE news_comments ADD CONSTRAINT FK_16A0357B70BEE6D FOREIGN KEY (visitor_id) REFERENCES visitors (id) ON DELETE SET NULL"
        );
        $this->execMutateSql(
            "ALTER TABLE pretickets_content ADD CONSTRAINT FK_E1110A2570BEE6D FOREIGN KEY (visitor_id) REFERENCES visitors (id) ON DELETE SET NULL"
        );
        $this->execMutateSql(
            "ALTER TABLE ratings ADD CONSTRAINT FK_CEB607C970BEE6D FOREIGN KEY (visitor_id) REFERENCES visitors (id) ON DELETE SET NULL"
        );
        $this->execMutateSql(
            "ALTER TABLE searchlog ADD CONSTRAINT FK_8C79CD5C70BEE6D FOREIGN KEY (visitor_id) REFERENCES visitors (id) ON DELETE SET NULL"
        );
        $this->execMutateSql(
            "ALTER TABLE sessions ADD CONSTRAINT FK_9A609D1370BEE6D FOREIGN KEY (visitor_id) REFERENCES visitors (id) ON DELETE SET NULL"
        );
        $this->execMutateSql(
            "ALTER TABLE tickets_messages ADD CONSTRAINT FK_3A9962E270BEE6D FOREIGN KEY (visitor_id) REFERENCES visitors (id) ON DELETE SET NULL"
        );
        $this->execMutateSql(
            "ALTER TABLE visitor_tracks ADD CONSTRAINT FK_E002459270BEE6D FOREIGN KEY (visitor_id) REFERENCES visitors (id) ON DELETE CASCADE"
        );
    }
}
