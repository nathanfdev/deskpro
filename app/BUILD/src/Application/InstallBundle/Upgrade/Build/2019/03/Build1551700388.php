<?php

namespace Application\InstallBundle\Upgrade\Build;

use Elastica\Request;

class Build1551700388 extends AbstractBuild implements OnlineBuildInterface
{
    public function addNewTables()
    {
    }

    public function runAlters()
    {
    }

    public function run()
    {
        $enabled = $this->container->getDb()->fetchColumn("SELECT `value` FROM `settings` WHERE `name` = 'elastica.enabled'");
        if ($enabled) {
            $this->out('Elasticsearch is enabled. Updating schema');
            $index = $this->container->get('fos_elastica.index.deskpro');
            $paths = [
                'ticket',
                'person',
                'organization',
                'feedback',
                'chat_conversation',
                'article',
            ];

            foreach ($paths as $path) {
                $index->getClient()->request('deskpro/_mapping/'.$path, Request::PUT, [
                    'properties' => [
                    'custom_data2' => ['type' => 'nested', 'properties' => [
                        'id'    => ['type' => 'integer'],
                        'value' => ['type' => 'string', 'analyzer' => 'text_content_analyzer'],
                    ]],
                ], ]);
            }
        }
    }
}
