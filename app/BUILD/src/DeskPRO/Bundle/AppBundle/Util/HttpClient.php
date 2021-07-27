<?php

namespace DeskPRO\Bundle\AppBundle\Util;

use Composer\CaBundle\CaBundle;
use DeskPRO\Component\Filesystem\SafeFile;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;
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

        $handler = isset($config['handler']) ? $config['handler'] : HandlerStack::create();

        $config['handler'] = $handler;

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
        if (!preg_match('/^https?:\/\//i', $url)) {
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

        if (defined('CURLOPT_PROTOCOLS')) {
            curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
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
        if (!preg_match('/^https?:\/\//i', $fromUrl)) {
            throw new \InvalidArgumentException();
        }

        SafeFile::assertValid($toPath, $expectBasePath);

        $fp = @fopen($toPath, 'w');
        if (!$fp) {
            throw new \RuntimeException('Could not open file for writing');
        }

        try {
            $bytesRead = self::streamFile($fromUrl, function ($chunk) use ($fp) {
                @fwrite($fp, $chunk);
            });
        } catch (\Exception $e) {
            @fclose($fp);
            throw $e;
        }

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
     * @return int bytes read
     */
    public static function streamFile($fromUrl, $handler, array $options = [])
    {
        if (!preg_match('/^https?:\/\//i', $fromUrl)) {
            throw new \InvalidArgumentException();
        }

        $options = array_merge([
            'timeout' => 6,
            'maxSize' => 26214400,
        ], $options);

        $ch = self::curlInit($fromUrl);
        curl_setopt($ch, CURLOPT_TIMEOUT, $options['timeout']);

        $bytesRead = 0;
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($curl, $body) use ($handler, $options, &$bytesRead) {
            $len = strlen($body);

            if(($bytesRead+$len) > $options['maxSize']) {
                throw new \RuntimeException('maxSize exceeded');
            }

            $handler($body);
            $bytesRead += $len;

            return $len;
        });

        curl_exec($ch);
        curl_close($ch);

        return $bytesRead;
    }

    /**
     * @param $fromUrl
     * @param array $options
     * @return string
     */
    public static function downloadToString($fromUrl, array $options = [])
    {
        $buf = '';
        self::streamFile($fromUrl, function ($dat) use (&$buf) {
            $buf .= $dat;
        }, $options);

        return $buf;
    }
}
