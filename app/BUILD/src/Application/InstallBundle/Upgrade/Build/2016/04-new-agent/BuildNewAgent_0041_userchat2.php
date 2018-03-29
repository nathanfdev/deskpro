<?php

/**
 * DeskPRO.
 */

namespace Application\InstallBundle\Upgrade\Build;

class BuildNewAgent_0041_userchat2 extends AbstractBuild
{
    public function run()
    {
        $this->execSlowAlterTable('chat_messages', 'ADD is_user TINYINT(1) NOT NULL DEFAULT 0');
    }
}

//[[build:1460678415]]
