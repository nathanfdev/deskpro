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

namespace Orb\Sms\Provider;

use Clickatell\Rest;

class ClickatellRest extends Rest
{
    /**
     * @var string
     */
    private $apiToken = '';

    public function __construct($apiToken)
    {
        $this->apiToken = $apiToken;
        parent::__construct($apiToken);
    }

    protected function curl($uri, $data)
    {
        // Force data object to array
        $data = $data ? (array) $data : $data;

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: '.$this->apiToken,
        ];

        // This is the clickatell endpoint. It doesn't really change so
        // it's safe for us to "hardcode" it here.
        $endpoint = static::API_URL.'/'.$uri;

        $curlInfo = curl_version();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, static::AGENT.' curl/'.$curlInfo['version'].' PHP/'.phpversion());

        global $DP_ENV;
        $proxy = $DP_ENV->getConfig('settings.http_client.proxy');
        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }

        // Specify the raw post data
        if ($data) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $result   = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        return $this->handle($result, $httpCode);
    }
}
