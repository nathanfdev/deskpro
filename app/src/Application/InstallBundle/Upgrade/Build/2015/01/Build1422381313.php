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

class Build1422381313 extends AbstractBuild
{
    public function run()
    {
        $this->out("content slug history");
		$this->execMutateSql("CREATE TABLE articles_slug_history (id INT AUTO_INCREMENT NOT NULL, article_id INT DEFAULT NULL, date_created DATETIME NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_566C1983989D9B62 (slug), INDEX IDX_566C19837294869C (article_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("CREATE TABLE downloads_slug_history (id INT AUTO_INCREMENT NOT NULL, download_id INT DEFAULT NULL, date_created DATETIME NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_F670233A989D9B62 (slug), INDEX IDX_F670233AC667AEAB (download_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("CREATE TABLE feedback_slug_history (id INT AUTO_INCREMENT NOT NULL, feedback_id INT DEFAULT NULL, date_created DATETIME NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_F0FF9966989D9B62 (slug), INDEX IDX_F0FF9966D249A887 (feedback_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("CREATE TABLE news_slug_history (id INT AUTO_INCREMENT NOT NULL, news_id INT DEFAULT NULL, date_created DATETIME NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8A3714CD989D9B62 (slug), INDEX IDX_8A3714CDB5A459A0 (news_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
		$this->execMutateSql("ALTER TABLE articles_slug_history ADD CONSTRAINT FK_566C19837294869C FOREIGN KEY (article_id) REFERENCES articles (id)");
		$this->execMutateSql("ALTER TABLE downloads_slug_history ADD CONSTRAINT FK_F670233AC667AEAB FOREIGN KEY (download_id) REFERENCES downloads (id)");
		$this->execMutateSql("ALTER TABLE feedback_slug_history ADD CONSTRAINT FK_F0FF9966D249A887 FOREIGN KEY (feedback_id) REFERENCES feedback (id)");
		$this->execMutateSql("ALTER TABLE news_slug_history ADD CONSTRAINT FK_8A3714CDB5A459A0 FOREIGN KEY (news_id) REFERENCES news (id)");
    }
}