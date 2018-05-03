<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1488913454 extends AbstractBuild
{
    public function run()
    {
        $this->out('Update date_last_message field for classes');
        $this->execDbQuery('default', 'ALTER TABLE agent_chat CHANGE date_last_message date_last_message DATETIME DEFAULT NULL');
        $this->execDbQuery('default', '
          UPDATE `agent_chat` AS `ac` 
          LEFT JOIN `agent_chat_message` AS `acm` ON `ac`.`id` = `acm`.`agent_chat_id`
          SET `ac`.`date_last_message` = NULL
          WHERE `ac`.`date_last_message` = `ac`.`date_created`
          AND `acm`.`id` IS NULL
        ');
    }
}
