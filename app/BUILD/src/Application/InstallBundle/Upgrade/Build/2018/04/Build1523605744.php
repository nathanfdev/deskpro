<?php

namespace Application\InstallBundle\Upgrade\Build;

// NOTE: I used the OnlineBuildInterface interface because
//       it looks like your schema changes ARE backwards compatible with the previous version.
//       You should double-check this yourself though. If there are breaking changes, use BlockingBuildInterface instead.

// NOTE: I have added the SkipPostBuildInterface interface because
//       it looks like you do not have any changes that require PostBuild to run.
//       You should double-check this yourself though. Remove the SkipPostBuildInterface interface if necessary.

// Please remove these NOTE comments after you have checked the code.

class Build1523605744 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE person_to_brand (person_id INT NOT NULL, brand_id INT NOT NULL, INDEX IDX_83286D5F217BBB47 (person_id), INDEX IDX_83286D5F44F5D008 (brand_id), PRIMARY KEY(person_id, brand_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE person_to_brand ADD CONSTRAINT FK_83286D5F217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE');
        $this->execDbQuery('default', 'ALTER TABLE person_to_brand ADD CONSTRAINT FK_83286D5F44F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
    }

    public function run()
    {
    }
}
