<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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
