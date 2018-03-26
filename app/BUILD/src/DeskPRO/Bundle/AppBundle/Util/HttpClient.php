<?php

namespace DeskPRO\Bundle\AppBundle\Util;

use Composer\CaBundle\CaBundle;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * @method ResponseInterface post(string|UriInterface $uri, array $options = [])
 * Class HttpClient
 */
class HttpClient extends Client
{
    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        global $DP_ENV;
        $usSysCABundle = (bool) $DP_ENV->getConfig('settings.http_client.use_sys_ca_bundle');

        $proxy = $DP_ENV->getConfig('settings.http_client.proxy');
        if ($proxy && empty($config[RequestOptions::PROXY])) {
            $config[RequestOptions::PROXY] = $proxy;
        }

        // cp from \DeskPRO_LowUtil_RequestCurl::setCaBundle
        if (false !== @$config[RequestOptions::VERIFY] && !$usSysCABundle) {
            $config[RequestOptions::VERIFY] = CaBundle::getBundledCaBundlePath();
        }

        parent::__construct($config);
    }
}
