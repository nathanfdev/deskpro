<?php

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

//[[build:1460678407]]
