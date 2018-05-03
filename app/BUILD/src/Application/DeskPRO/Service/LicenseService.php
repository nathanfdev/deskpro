<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;

class LicenseService
{
    /**
     * @return array
     */
    public static function getLatestVersion()
    {
        static $latest = null;

        if ($latest === null) {
            $latest = self::fetchServiceResult('check-latest-version.json', ['my_build' => DP_BUILD_TIME]);
        }

        return $latest;
    }

    /**
     * Compares current build to the latest build available.
     *
     * Data returned:
     * - build: <timestamp>
     * - build_link: <url>
     * - your_build: <timestamp>
     * - count_behind: <int>
     *
     * @return array
     */
    public static function compareVersion()
    {
        static $data = null;

        if ($data === null) {
            try {
                $data = self::fetchServiceResult('build/compare-version.json', ['my_build' => DP_BUILD_TIME]);
            } catch (\Exception $e) {
                $data = [];
            }
        }

        return $data;
    }

    /**
     * Get version notice info.
     *
     * Data returned:
     * - link: <url>
     * - message: <text>
     * - level: notice/warning/critical
     *
     * @return array
     */
    public static function getVersionNotices()
    {
        static $data = null;

        if ($data === null) {
            try {
                $data = self::fetchServiceResult('build/version-notices.json', ['my_build' => defined('DP_BUILD_TIME') ? DP_BUILD_TIME : 0]);
            } catch (\Exception $e) {
                $data = [];
            }
        }

        return $data;
    }

    /**
     * Gets news from RSS feed.
     *
     * @return array|null
     */
    public static function getNews()
    {
        $news = [];

        try {
            $client = new Client([
                'base_uri'             => \DpSys\License::getSupportUrl(),
                RequestOptions::VERIFY => false,
            ]);

            try {
                $response = $client->get('/news/2-product.rss');
            } catch (ClientException $e) {
                return;
            }

            $rss = @simplexml_load_string((string) $response->getBody());
            unset($r);

            if (!$rss || empty($rss) || empty($rss->channel->item)) {
                return;
            }

            $x = 0;
            foreach ($rss->channel->item as $item) {
                $news[] = [
                    'title' => (string) $item->title,
                    'link'  => (string) $item->link,
                ];
                if ($x++ > 5) {
                    break;
                }
            }
        } catch (\Exception $e) {
            return;
        }

        return $news;
    }

    /**
     * @param string $endpoint
     * @param array  $post_data
     *
     * @return array
     */
    public static function fetchServiceResult($endpoint, array $post_data = [])
    {
        try {
            $client = new Client([
                'base_uri'                      => \DpSys\License::getSecureLicServer(),
                RequestOptions::VERIFY          => false,
                RequestOptions::ALLOW_REDIRECTS => ['strict' => true],
            ]);

            $response = $client->post('/api/'.ltrim($endpoint, '/'), [RequestOptions::FORM_PARAMS => $post_data]);
            $result   = (string) $response->getBody();
        } catch (\Exception $e) {
            $result = '';
        }

        if (!$result) {
            throw new \RuntimeException("No response from server: $url $result");
        }

        $res_data = json_decode($result, true);
        if (!is_array($res_data)) {
            throw new \RuntimeException("Invalid JSON response from server: $url $result");
        }

        return $res_data;
    }
}
