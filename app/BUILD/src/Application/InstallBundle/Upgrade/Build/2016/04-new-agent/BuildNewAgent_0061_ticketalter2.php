<?php

/**
 * DeskPRO.
 */

namespace Application\InstallBundle\Upgrade\Build;

class BuildNewAgent_0061_ticketalter2 extends AbstractBuild
{
    public function run()
    {
        $sh           = $this->getSchemaHelper();
        $instructions = [];

        if ($fk = $sh->findForeignKey('tickets_messages', 'visitor_id', 'visitors', 'id')) {
            $instructions[] = 'DROP FOREIGN KEY '.$fk->getName();
        }
        if ($idx = $sh->findIndex('tickets_messages', 'visitor_id')) {
            $instructions[] = 'DROP INDEX '.$idx->getName();
        }

        $instructions[] = 'DROP visitor_id, ADD visitor_id VARCHAR(120) NULL DEFAULT NULL';

        $this->execSlowAlterTable('tickets_messages', implode(', ', $instructions));
    }
}

//[[build:1460678420]]
