<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1568210054 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE splash_image_property (id INT AUTO_INCREMENT NOT NULL, blob_id INT DEFAULT NULL, urn VARCHAR(255) NOT NULL, options LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', UNIQUE INDEX UNIQ_2C1DD95EED3E8EA5 (blob_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE splash_image_property ADD CONSTRAINT FK_2C1DD95EED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $tables = [
            'news'             => '1DD39950339FD429',
            'downloads'        => '4B73A4B5339FD429',
            'community_topics' => 'E03CB3CA339FD429',
            'topics'           => '91F64639339FD429',
            'articles'         => 'BFDD3168339FD429',
        ];

        foreach ($tables as $table => $index) {
            $instructions   = [];
            $instructions[] = 'ADD splash_image_property_id INT DEFAULT NULL';
            $instructions[] = 'ADD CONSTRAINT FK_'.$index.' FOREIGN KEY (splash_image_property_id) REFERENCES splash_image_property (id) ON DELETE SET NULL';
            $instructions[] = 'ADD INDEX IDX_'.$index.' (splash_image_property_id)';
            $this->execSlowAlterTable($table, implode(', ', $instructions));
        }
    }
}
