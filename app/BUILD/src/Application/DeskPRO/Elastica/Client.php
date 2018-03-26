<?php

namespace Application\DeskPRO\Elastica;

/*
 * DeskPRO
 *
 * @package DeskPRO
 */

use FOS\ElasticaBundle\Client as BaseClient;

class Client extends BaseClient
{
    protected function _initConnections()
    {
        // doubleslashes aren't accepted anymore since ES 5.*
        // see Elastica\Transport\Http:58
        $config         = $this->getConfig();
        $config['path'] = ltrim('/', @$config['path']);
        $this->setConfig($config);

        parent::_initConnections();

        // Adds empty 'headers' config or else logger on
        // BaseClient will cause exception
        foreach ($this->getConnections() as $conn) {
            if (!$conn->hasConfig('headers')) {
                $conn->addConfig('headers', []);
            }
        }
    }
}
