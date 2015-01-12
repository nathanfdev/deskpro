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

class Build1421086353 extends AbstractBuild
{
    public function run()
    {
        $this->out("add news and download subscriptions");
		$this->execMutateSql("CREATE TABLE download_subscriptions (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, download_id INT DEFAULT NULL, category_id INT DEFAULT NULL, INDEX IDX_23B05F1D217BBB47 (person_id), INDEX IDX_23B05F1DC667AEAB (download_id), INDEX IDX_23B05F1D12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("CREATE TABLE news_subscriptions (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, news_id INT DEFAULT NULL, category_id INT DEFAULT NULL, INDEX IDX_5194E647217BBB47 (person_id), INDEX IDX_5194E647B5A459A0 (news_id), INDEX IDX_5194E64712469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("ALTER TABLE download_subscriptions ADD CONSTRAINT FK_23B05F1D217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE");
		$this->execMutateSql("ALTER TABLE download_subscriptions ADD CONSTRAINT FK_23B05F1DC667AEAB FOREIGN KEY (download_id) REFERENCES downloads (id) ON DELETE CASCADE");
		$this->execMutateSql("ALTER TABLE download_subscriptions ADD CONSTRAINT FK_23B05F1D12469DE2 FOREIGN KEY (category_id) REFERENCES download_categories (id) ON DELETE CASCADE");
		$this->execMutateSql("ALTER TABLE news_subscriptions ADD CONSTRAINT FK_5194E647217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE");
		$this->execMutateSql("ALTER TABLE news_subscriptions ADD CONSTRAINT FK_5194E647B5A459A0 FOREIGN KEY (news_id) REFERENCES news (id) ON DELETE CASCADE");
		$this->execMutateSql("ALTER TABLE news_subscriptions ADD CONSTRAINT FK_5194E64712469DE2 FOREIGN KEY (category_id) REFERENCES news_categories (id) ON DELETE CASCADE");
    }
}