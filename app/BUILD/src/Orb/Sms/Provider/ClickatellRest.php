<?php

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
