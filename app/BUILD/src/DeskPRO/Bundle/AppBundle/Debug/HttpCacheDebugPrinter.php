<?php

namespace DeskPRO\Bundle\AppBundle\Debug;

use Symfony\Component\HttpKernel\HttpCache\HttpCache;

class HttpCacheDebugPrinter
{
    public static function debugPortalCacheKernel(HttpCache $http_cache)
    {
        $log      = $http_cache->getLog();
        $log_rows = explode(';', $log);
        $log_rows = array_map(function ($part) {
            return trim($part);
        }, $log_rows);

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
                'resource_or_tag_name' => [$resource, $resource],
                'tag:options'          => '',
                'tag:lang'             => '',
                'tag:theme_set_id'     => '',
                'tag:brand_id'         => '',
                'tag:controller'       => '',
                'method'               => $method,
            ];

            if ('tag' == $row_type) {
                $url_parts   = self::getResourceArray($resource);
                $tag_name    = $url_parts['_path']['_tag_name'];
                $controller  = $url_parts['_path']['_controller'];
                $tag_options = [];
                foreach ($url_parts as $part => $v) {
                    if (strpos($part, 'tag_options') === 0) {
                        $key = substr($part, strlen('tag_options['), -1);
                        if ($key === '_tag_name') {
                            continue;
                        }
                        $tag_options[$key] = $v;
                    }
                }
                $tag_options  = json_encode($tag_options);
                $tag_lang     = $url_parts['lang_url_code'];
                $theme_set_id = $url_parts['theme_set_id'];
                $brand_id     = $url_parts['brand_id'];

                $parsed_row['resource_or_tag_name'] = [$tag_name, $resource];
                $parsed_row['tag:lang']             = $tag_lang;
                $parsed_row['tag:controller']       = $controller;
                $parsed_row['tag:theme_set_id']     = $theme_set_id;
                $parsed_row['tag:brand_id']         = $brand_id;
                $parsed_row['tag:options']          = $tag_options;
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
            foreach ($row as $key => $td) {
                if ($key === 'resource_or_tag_name') {
                    $html .= '<td><a href="#" title="'.$td[1].'">'.$td[0].'</a></td>';
                } else {
                    $html .= '<td>'.$td.'</td>';
                }
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';

        $html .= '</table>';

        return $html;
    }

    private static function getResourceArray($resource)
    {
        $u  = substr($resource, strpos($resource, '?') + 1);
        $u  = explode('&', $u);
        $pp = array_map(function ($pair) {
            return explode('=', $pair);
        }, $u);
        $data = [];
        foreach ($pp as $vv) {
            $data[urldecode($vv[0])] = urldecode($vv[1]);
        }

        $data['_path'] = \GuzzleHttp\Psr7\parse_query($data['_path']);

        return $data;
    }
}
