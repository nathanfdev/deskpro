<?php

namespace Application\DeskPRO\Elastica;

/*
 * DeskPRO
 *
 * @package DeskPRO
 */

use Application\DeskPRO\Proxy\OutboundHttpProxy;
use Elastica\Request;
use FOS\ElasticaBundle\Elastica\Client as BaseClient;

class Client extends BaseClient
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var OutboundHttpProxy
     */
    private $proxy;

    /**
     * @param OutboundHttpProxy $proxy
     */
    public function setOutboundProxy(OutboundHttpProxy $proxy)
    {
        $this->proxy = $proxy;
    }

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

    public function request($path, $method = Request::GET, $data = array(), array $query = array())
    {
        global $DP_ENV;

        if (OutboundHttpProxy::isUsingProxy() && $this->proxy) {
            $proxyUrlParts = parse_url($DP_ENV->getConfig('env.http_proxy_url'));

            $conn = $this->getConnection();

            if (!$this->host) {
                $this->host = $conn->getHost();
            }

            $serviceToken = $this->proxy->getElasticsearchServiceToken(
                DPC_SITE_ID,
                strtolower($conn->getTransport()),
                $path,
                $conn->getPort(),
                $method
            );

            if (empty($proxyUrlParts["port"])) {
                $port = $proxyUrlParts["scheme"] == "https" ? 443 : 80;
            } else {
                $port = $proxyUrlParts["scheme"];
            }

            $conn->addConfig('headers', [
                'ProxyAuthorization' => 'Bearer ' . $serviceToken,
                'X-Forward-To' => $this->host,
            ]);
            $conn->setHost($proxyUrlParts['host']);
            $conn->setTransport(ucfirst($proxyUrlParts['scheme']));
            $conn->setPort((int) $port);

            $method = "POST"; // API Gateway excludes a GET body, so change it to a post @see https://github.com/elastic/elasticsearch/issues/16024#issuecomment-172491501
            $path   = "elasticsearch/{$path}";
        }


        return parent::request($path, $method, $data, $query);
    }
}
