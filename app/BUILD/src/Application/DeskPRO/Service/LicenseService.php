<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
            $latest = self::fetchServiceResult('check-latest-version.json', array('my_build' => DP_BUILD_TIME));
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
                $data = self::fetchServiceResult('build/compare-version.json', array('my_build' => DP_BUILD_TIME));
            } catch (\Exception $e) {
                $data = array();
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
                $data = self::fetchServiceResult('build/version-notices.json', array('my_build' => defined('DP_BUILD_TIME') ? DP_BUILD_TIME : 0));
            } catch (\Exception $e) {
                $data = array();
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
        $news = array();

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
                $news[] = array(
                    'title' => (string) $item->title,
                    'link'  => (string) $item->link,
                );
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
    public static function fetchServiceResult($endpoint, array $post_data = array())
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
