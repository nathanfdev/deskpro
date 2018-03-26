<?php

/**
 * DeskPRO.
 */

namespace Application\InstallBundle\Upgrade\Build;

class BuildNewAgent_0040_userchat1 extends AbstractBuild
{
    public function run()
    {
        $this->execSlowAlterTable('chat_conversations', "ADD date_agent_typing DATETIME DEFAULT NULL, ADD email_validation_code VARCHAR(15) NOT NULL DEFAULT '', ADD email_validated TINYINT(1) NOT NULL DEFAULT 0");
    }
}

//[[build:1460678414]]
