<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Delivery;

use Application\DeskPRO\Proxy\OutboundHttpProxy;

class PusherClient extends \Pusher
{
    /**
     * @var OutboundHttpProxy
     */
    private $proxy;

    /**
     * @var resource
     */
    private $ch;

    /**
     * Constructor
     *
     * @param OutboundHttpProxy $proxy
     * @param $auth_key
     * @param $secret
     * @param $app_id
     * @param array $options
     * @param null $host
     * @param null $port
     * @param null $timeout
     */
    public function __construct(OutboundHttpProxy $proxy, $auth_key, $secret, $app_id, $options = array(), $host = null, $port = null, $timeout = null)
    {
        parent::__construct($auth_key, $secret, $app_id, $options, $host, $port, $timeout);
        $this->proxy = $proxy;
    }

    /**
     * {@inheritDoc}
     */
    public function triggerBatch($batch = array(), $debug = false, $already_encoded = false)
    {
        $query_params = array();

        $settings = $this->getSettings();

        $s_url = $settings['base_path'].'/batch_events';

        if (!$already_encoded) {
            foreach ($batch as $key => $event) {
                if (!is_string($event['data'])) {
                    $batch[$key]['data'] = json_encode($event['data']);
                }
            }
        }

        $post_params = array();
        $post_params['batch'] = $batch;

        $post_value = json_encode($post_params);

        $query_params['body_md5'] = md5($post_value);

        $ch = $this->create_curl($this->ddn_domain(), $s_url, 'POST', $query_params);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_value);

        $response = $this->exec_curl($ch);

        if ($response['status'] === 200 && $debug === false) {
            return true;
        } elseif ($debug === true || $settings['debug'] === true) {
            return $response;
        }

        return false;
    }

    private function create_curl($domain, $s_url, $request_method = 'GET', $query_params = array())
    {
        global $DP_ENV;

        $httpProxyUrl = $DP_ENV->getConfig('env.http_proxy_url');

        if (empty($httpProxyUrl)) {
            throw new \PusherException('HTTP product URL must be configured via the env var DP_HTTP_PROXY_URL');
        }

        $settings = $this->getSettings();

        // Create the signed signature...
        $signed_query = self::build_auth_query_string(
            $settings['auth_key'],
            $settings['secret'],
            $request_method,
            $s_url,
            $query_params);

        $originalHost = parse_url($domain, PHP_URL_HOST);

        $domain = $httpProxyUrl.'/pusher';

        $full_url = $domain.$s_url.'?'.$signed_query;

        // Create or reuse existing curl handle
        if (!$this->ch) {
            $this->ch = curl_init();
        }

        if ($this->ch === false) {
            throw new \PusherException('Could not initialise cURL!');
        }

        $ch = $this->ch;

        // curl handle is not reusable unless reset
        if (function_exists('curl_reset')) {
            curl_reset($ch);
        }

        // Set cURL opts and execute request
        curl_setopt($ch, CURLOPT_URL, $full_url);

        $proxyServiceToken = $this->proxy->getPusherServiceToken(
            DPC_SITE_ID,
            $settings['app_id'],
            $settings['auth_key']
        );

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Expect:',
            'X-Pusher-Library: pusher-http-php '.self::$VERSION,
            'Proxy-Authorization: Bearer '.$proxyServiceToken,
            'X-Forward-To: '.$originalHost,
        ));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $settings['timeout']);
        if ($request_method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
        } elseif ($request_method === 'GET') {
            curl_setopt($ch, CURLOPT_POST, 0);
        } // Otherwise let the user configure it

        // Set custom curl options
        if (!empty($settings['curl_options'])) {
            foreach ($settings['curl_options'] as $option => $value) {
                curl_setopt($ch, $option, $value);
            }
        }

        return $ch;
    }

    private function exec_curl($ch)
    {
        $response = array();

        $response['body'] = curl_exec($ch);
        $response['status'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        return $response;
    }

    private function ddn_domain()
    {
        $settings = $this->getSettings();

        return $settings['scheme'].'://'.$settings['host'].':'.$settings['port'];
    }
}
