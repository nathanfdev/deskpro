<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1488277383 extends AbstractBuild
{
    public function run()
    {
        $this->out('Upgrade Task Links');
        $fk = $this->getSchemaHelper()->findForeignKey('task_links', 'chat_id', 'chat_conversations', 'id');
        if ($fk) {
            $this->out("-- Drop existing FK {$fk->getName()}");
            $this->execDbQuery('default', "ALTER TABLE task_links DROP FOREIGN KEY {$fk->getName()}");
        }

        $this->out('-- Create new FK');
        $this->execDbQuery('default', 'ALTER TABLE task_links ADD CONSTRAINT FK_1626BA381A9A7125 FOREIGN KEY (chat_id) REFERENCES chat_conversations (id) ON DELETE CASCADE');
    }
}
