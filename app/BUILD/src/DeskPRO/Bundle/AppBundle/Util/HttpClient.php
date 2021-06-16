<?php

namespace DeskPRO\Bundle\AppBundle\Util;

use Composer\CaBundle\CaBundle;
use DeskPRO\Component\Filesystem\SafeFile;
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

        $proxy = $DP_ENV->getConfig('settings.http_client.proxy');
        $noProxy = $DP_ENV->getConfig('settings.http_client.no_proxy');

        if ($proxy && empty($config[RequestOptions::PROXY])) {
            $config[RequestOptions::PROXY] = [
                'http' => $proxy,
                'https' => $proxy,
                'no' => $noProxy ?: null
            ];
        }

        $usSysCABundle = (bool) $DP_ENV->getConfig('settings.http_client.use_sys_ca_bundle');
        // True by default
        $doVerify = isset($config[RequestOptions::VERIFY]) ? false !== @$config[RequestOptions::VERIFY] : true;
        // cp from \DeskPRO_LowUtil_RequestCurl::setCaBundle
        if ($doVerify && !$usSysCABundle) {
            $config[RequestOptions::VERIFY] = CaBundle::getBundledCaBundlePath();
        }

        parent::__construct($config);
    }

    /**
     * Wraps curl_init to add default proxy.
     *
     * @deprecated you should probably be using HttpClient itself
     *
     * @param string|null $url
     * @return false|resource
     */
    public static function curlInit($url = null)
    {
        $url = strtolower($url);
        if (!preg_match('/^https?:\/\//', $url)) {
            throw new \InvalidArgumentException();
        }

        global $DP_ENV;

        $ch = curl_init($url);

        $proxy = $DP_ENV->getConfig('settings.http_client.proxy');
        $noProxy = $DP_ENV->getConfig('settings.http_client.no_proxy');

        if ($proxy && $noProxy) {
            if (\GuzzleHttp\is_host_in_noproxy(parse_url($url, PHP_URL_HOST), $noProxy)) {
                $proxy = null;
            }
        }
        if ($proxy) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }

        return $ch;
    }

    /**
     * Like copy() but uses the proxy. Also adds $options to limit max file size.
     *
     * @param string $fromUrl         The URL to download
     * @param string $toPath          The target file to write
     * @param string $expectBasePath  Verify the base path that $toPath shuold reside in (security precaution)
     * @param array $options          Options
     * @return int
     */
    public static function downloadFile($fromUrl, $toPath, $expectBasePath, array $options = [])
    {
        $fromUrl = strtolower($fromUrl);
        if (!preg_match('/^https?:\/\//', $fromUrl)) {
            throw new \InvalidArgumentException();
        }

        $options = array_merge([
            'read_timeout' => 10,
            'connect_timeout' => 10,
            'maxSize' => 26214400
        ], $options);

        SafeFile::assertValid($toPath, $expectBasePath);

        $clientOptions = [
            'read_timeout' => $options['read_timeout'],
            'connect_timeout' => $options['connect_timeout'],
        ];

        $client = new self($clientOptions);
        $response = $client->get($fromUrl, [
            'stream' => true,
        ]);
        $body = $response->getBody();

        $fp = @fopen($toPath, 'w');
        if (!$fp) {
            throw new \RuntimeException('Could not open file for writing');
        }

        $bytesRead = 0;
        while (!$body->eof()) {
            $dat = $body->read(1024);
            @fwrite($fp, $dat);
            $bytesRead += strlen($dat);

            if($bytesRead > $options['maxSize']) {
                $body->close();
                throw new \RuntimeException("exceeded maxSize");
            }
        }

        $body->close();
        fclose($fp);

        if (!$bytesRead) {
            throw new \RuntimeException("nothing read");
        }

        return $bytesRead;
    }

    /**
     * @param string $fromUrl         The URL to download
     * @param $handler                Callback to call for each block of data read. Return false to abort reading.
     * @param array $options
     */
    public static function streamFile($fromUrl, $handler, array $options = [])
    {
        $fromUrl = strtolower($fromUrl);
        if (!preg_match('/^https?:\/\//', $fromUrl)) {
            throw new \InvalidArgumentException();
        }

        $options = array_merge([
            'read_timeout' => 10,
            'connect_timeout' => 10,
            'maxSize' => 26214400,
            'chunkSize' => 2048
        ], $options);

        $clientOptions = [
            'read_timeout' => $options['read_timeout'],
            'connect_timeout' => $options['connect_timeout'],
        ];

        $client = new self($clientOptions);
        $response = $client->get($fromUrl, [
            'stream' => true,
        ]);
        $body = $response->getBody();

        $bytesRead = 0;
        while (!$body->eof()) {
            $dat = $body->read($options['chunkSize']);
            $bytesRead += strlen($dat);

            if($bytesRead > $options['maxSize']) {
                $body->close();
                throw new \RuntimeException("exceeded maxSize");
            }

            if (call_user_func($handler, $dat) === false) {
                return $bytesRead;
            }
        }

        $body->close();

        return $bytesRead;
    }
}
