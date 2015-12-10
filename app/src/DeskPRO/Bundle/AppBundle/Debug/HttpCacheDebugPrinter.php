<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Debug;

use Symfony\Component\HttpKernel\HttpCache\HttpCache;

class HttpCacheDebugPrinter
{
    public static function debugPortalCacheKernel(HttpCache $http_cache)
    {
        $log      = $http_cache->getLog();
        $log_rows = explode(';', $log);
        $log_rows = array_map(function ($part) { return trim($part); }, $log_rows);

        $parsed_rows = [];
        foreach ($log_rows as $row) {
            $row_type = strpos($row, '_tag_name') ? 'tag' : 'page';

            $row_parts = explode(' ', $row);

            $method = $row_parts[0];

            // only 0 is known, but the rest of the log string has to be parsed further
            unset($row_parts[0]);

            $remaining_log_line = implode(' ', $row_parts);
            $remaining_parts    = explode(': ', $remaining_log_line);

            $resource     = $remaining_parts[0];
            $cache_result = $remaining_parts[1];

            $parsed_row = [
                'cache_result'         => $cache_result,
                'resource_or_tag_name' => $resource,
                'tag_lang'             => '',
                'tag_mode'             => '',
                'method'               => $method,
            ];

            if ('tag' == $row_type) {
                $url       = urldecode($resource);
                $url_parts = \GuzzleHttp\Psr7\parse_query($url);
                $tag_name  = $url_parts['_tag_name'];
                $tag_lang  = $url_parts['lang_url_code'];
                $tag_mode  = '';
                if ($m = unserialize($url_parts['_portal_mode'])) {
                    $tag_mode = (string) $m;
                }

                $parsed_row['resource_or_tag_name'] = $tag_name;
                $parsed_row['tag_lang']             = $tag_lang;
                $parsed_row['tag_mode']             = $tag_mode;
            }

            $parsed_rows[] = $parsed_row;
        }

        return self::toHtmlTable($parsed_rows);
    }

    protected static function toHtmlTable($rows)
    {
        $html = '<table class="table data-table">';

        $html .= '<thead> <tr>';
        foreach ($rows[0] as $key => $val) {
            $html .= '<th>'.$key.'</th>';
        }
        $html .= '</tr> </thead>';

        reset($rows);
        $html .= '<tbody>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $td) {
                $html .= '<td>'.$td.'</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';

        $html .= '</table>';

        return $html;
    }
}
