<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace Application\InstallBundle\Upgrade\Build;

class BuildNewAgent_0020_misc extends AbstractBuild
{
    public function run()
    {
        $sh = $this->getSchemaHelper();

        $this->out('Modifying settings table');
        $this->execMutateSql('ALTER TABLE settings DROP PRIMARY KEY, ADD id INT AUTO_INCREMENT NOT NULL, ADD UNIQUE INDEX unique_setting_name (name), ADD PRIMARY KEY (id)');

        $this->out('Modifying templates table');
        $instructions = [];
        if ($fk = $sh->findForeignKey('templates', 'style_id', 'styles', 'id')) {
            $instructions[] = 'DROP FOREIGN KEY '.$fk->getName();
        }
        if ($idx = $sh->findIndex('templates', 'style_id')) {
            $instructions[] = 'DROP INDEX '.$idx->getName();
        }
        $instructions[] = 'DROP style_id';
        $instructions[] = 'ADD theme_set_id INT NULL DEFAULT NULL';
        $instructions[] = 'ADD CONSTRAINT FK_6F287D8EC0C33964 FOREIGN KEY (theme_set_id) REFERENCES theme_sets (id)';
        $instructions[] = 'ADD INDEX IDX_6F287D8EC0C33964 (theme_set_id)';
        $this->execMutateSql('ALTER TABLE templates '.implode(', ', $instructions));
    }
}

//[[build:1456790406]]

