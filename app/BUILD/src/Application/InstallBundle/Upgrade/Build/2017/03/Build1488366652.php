<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1488366652 extends AbstractBuild
{
    public function run()
    {
        $this->out('Add Content input fields');
        $this->execSlowAlterTable('articles', 'ADD content_input LONGTEXT NOT NULL');
        $this->execSlowAlterTable('articles', 'ADD content_input_type VARCHAR(100) DEFAULT NULL');
        $this->execSlowAlterTable('downloads', 'ADD content_input LONGTEXT NOT NULL');
        $this->execSlowAlterTable('downloads', 'ADD content_input_type VARCHAR(100) DEFAULT NULL');
        $this->execSlowAlterTable('feedback', 'ADD content_input LONGTEXT NOT NULL');
        $this->execSlowAlterTable('feedback', 'ADD content_input_type VARCHAR(100) DEFAULT NULL');
        $this->execSlowAlterTable('news', 'ADD content_input LONGTEXT NOT NULL');
        $this->execSlowAlterTable('news', 'ADD content_input_type VARCHAR(100) DEFAULT NULL');
    }
}
