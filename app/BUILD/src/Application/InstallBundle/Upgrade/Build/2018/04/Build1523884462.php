<?php

namespace Application\InstallBundle\Upgrade\Build;

// NOTE: I used the OnlineBuildInterface interface because
//       it looks like your schema changes ARE backwards compatible with the previous version.
//       You should double-check this yourself though. If there are breaking changes, use BlockingBuildInterface instead.

// NOTE: I have added the SkipPostBuildInterface interface because
//       it looks like you do not have any changes that require PostBuild to run.
//       You should double-check this yourself though. Remove the SkipPostBuildInterface interface if necessary.

// Please remove these NOTE comments after you have checked the code.

class Build1523884462 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE email_account_to_brand (email_account_id INT NOT NULL, brand_id INT NOT NULL, INDEX IDX_6199C26937D8AD65 (email_account_id), INDEX IDX_6199C26944F5D008 (brand_id), PRIMARY KEY(email_account_id, brand_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE email_account_to_brand ADD CONSTRAINT FK_6199C26937D8AD65 FOREIGN KEY (email_account_id) REFERENCES email_accounts (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE email_account_to_brand ADD CONSTRAINT FK_6199C26944F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE email_accounts ADD is_all_brands TINYINT(1) DEFAULT \'1\' NOT NULL');
    }

    public function run()
    {
    }
}
