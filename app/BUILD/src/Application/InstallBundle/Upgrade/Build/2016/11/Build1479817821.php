<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1479817821 extends AbstractBuild
{
    public function run()
    {
        $this->out('Add Person Onboarding table');
        $this->execDbQuery('default', 'CREATE TABLE person_onboarding (id INT AUTO_INCREMENT NOT NULL, person_id INT DEFAULT NULL, current_step INT DEFAULT NULL, onboarding_class VARCHAR(255) NOT NULL, status INT NOT NULL, application VARCHAR(255) NOT NULL, date_completion DATETIME DEFAULT NULL, INDEX IDX_151CD81B217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
        $this->execDbQuery('default', 'ALTER TABLE person_onboarding ADD CONSTRAINT FK_151CD81B217BBB47 FOREIGN KEY (person_id) REFERENCES people (id) ON DELETE CASCADE');
    }
}
