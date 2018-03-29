<?php

/**
 * DeskPRO.
 */

namespace Application\InstallBundle\Upgrade\Build;

class BuildNewAgent_0060_ticketalter1 extends AbstractBuild
{
    public function run()
    {
        $sh           = $this->getSchemaHelper();
        $instructions = [];

        if ($fk = $sh->findForeignKey('tickets', 'person_email_validating_id', 'people_emails_validating', 'id')) {
            $instructions[] = 'DROP FOREIGN KEY '.$fk->getName();
        }
        if ($idx = $sh->findIndex('tickets', 'person_email_validating_id')) {
            $instructions[] = 'DROP INDEX '.$idx->getName();
        }

        $instructions[] = 'DROP person_email_validating_id, DROP validating';
        $instructions[] = 'ADD brand_id INT DEFAULT NULL';
        $instructions[] = 'ADD CONSTRAINT FK_54469DF444F5D008 FOREIGN KEY (brand_id) REFERENCES brands (id) ON DELETE SET NULL';
        $instructions[] = 'ADD INDEX IDX_54469DF444F5D008 (brand_id)';

        $this->execSlowAlterTable('tickets', implode(', ', $instructions));
    }
}

//[[build:1460678419]]
