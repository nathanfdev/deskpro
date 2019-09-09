<?php

namespace Application\InstallBundle\Upgrade\Build;

class Build1568025284 extends AbstractBuild implements OnlineBuildInterface, SkipPostBuildInterface
{
    public function addNewTables()
    {
        $this->execDbQuery('default', 'CREATE TABLE icon_property (id INT AUTO_INCREMENT NOT NULL, blob_id INT DEFAULT NULL, urn VARCHAR(255) NOT NULL, options LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', UNIQUE INDEX UNIQ_96E733E1ED3E8EA5 (blob_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->execDbQuery('default', 'ALTER TABLE icon_property ADD CONSTRAINT FK_96E733E1ED3E8EA5 FOREIGN KEY (blob_id) REFERENCES blobs (id) ON DELETE CASCADE');
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $tables = [
            'news'                => '1DD399504646DDA',
            'community_channels'  => '15227914646DDA',
            'downloads'           => '4B73A4B54646DDA',
            'products'            => 'B3BA5A5A4646DDA',
            'article_categories'  => '62A97E94646DDA',
            'news_categories'     => 'D68C91114646DDA',
            'community_topics'    => 'E03CB3CA4646DDA',
            'topics'              => '91F646394646DDA',
            'download_categories' => '3317F154646DDA',
            'articles'            => 'BFDD31684646DDA',
        ];

        foreach ($tables as $table => $index) {
            $instructions   = [];
            $instructions[] = 'ADD icon_property_id INT DEFAULT NULL';
            $instructions[] = 'ADD CONSTRAINT FK_'.$index.' FOREIGN KEY (icon_property_id) REFERENCES icon_property (id) ON DELETE SET NULL';
            $instructions[] = 'ADD INDEX IDX_'.$index.' (icon_property_id)';
            $this->execSlowAlterTable($table, implode(', ', $instructions));
        }
    }
}
