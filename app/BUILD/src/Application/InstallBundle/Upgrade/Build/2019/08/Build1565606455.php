<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1565606455 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
        $this->execDbQuery('default', 'ALTER TABLE object_aliases ADD custom_def_article_id INT DEFAULT NULL, ADD custom_def_chat_id INT DEFAULT NULL');
        $this->execDbQuery('default', 'ALTER TABLE object_aliases ADD CONSTRAINT FK_5F5C3B99C0C42B82 FOREIGN KEY (custom_def_article_id) REFERENCES custom_def_article (id)');
        $this->execDbQuery('default', 'ALTER TABLE object_aliases ADD CONSTRAINT FK_5F5C3B99AEF199B7 FOREIGN KEY (custom_def_chat_id) REFERENCES custom_def_chat (id)');
        $this->execDbQuery('default', 'CREATE INDEX IDX_5F5C3B99C0C42B82 ON object_aliases (custom_def_article_id)');
        $this->execDbQuery('default', 'CREATE INDEX IDX_5F5C3B99AEF199B7 ON object_aliases (custom_def_chat_id)');
    }

    public function run()
    {
    }
}
