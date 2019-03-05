<?php

namespace Application\InstallBundle\Upgrade\Build;

use Application\DeskPRO\Elastica\IndexFactory;
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
        $enabled = $this->readSetting('elastica.enabled');
        if ($enabled) {
            $this->out('Elasticsearch is enabled. Updating schema');
            /** @var IndexFactory $indexFactory */
            $indexFactory = $this->container->get('deskpro.elastica.default_index_factory');
            $paths        = [
                'ticket',
                'person',
                'organization',
                'feedback',
                'chat_conversation',
                'article',
            ];
            $index = $indexFactory->getIndex('deskpro');
            foreach ($paths as $path) {
                $index->getClient()->request($index->getName().'/_mapping/'.$path, Request::PUT, [
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
