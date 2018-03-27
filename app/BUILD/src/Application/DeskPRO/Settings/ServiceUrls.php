<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Settings;

/**
 * Simple wrapper around fetching URLs eg. to a helpdesk article.
 * Removes the URLs from templates and files and puts them into a config instead
 * so it's easier to edit.
 */
class ServiceUrls
{
    /** @var array */
    protected $urls = [];

    /**
     * @param $file
     */
    public function loadPack($file)
    {
        $pack_urls = require $file;
        if (!$pack_urls) {
            $pack_urls = [];
        }

        $this->urls = array_merge($this->urls, $pack_urls);
    }

    /**
     * @param string $name         Name of the URL
     * @param array  $params       Query params to append to the URL
     * @param array  $named_params Named parameters in the URL {{somevar}}
     * @param bool   $html         True if this is going to be used in HTML. Arg separater becomes &amp;
     *
     * @return string
     */
    public function get($name, array $params = null, array $named_params = null, $html = true)
    {
        $url = isset($this->urls[$name]) ? $this->urls[$name] : '';

        if ($params) {
            if (strpos($url, '?') === false) {
                $url .= '?';
            } else {
                $url .= $html ? '&amp;' : '&';
            }

            $url .= http_build_query($params, null, $html ? '&amp;' : '&');
        }

        if ($named_params) {
            foreach ($named_params as $k => $v) {
                $url = str_replace(['{{'.$k.'}}', '{{ '.$k.' }}'], $v, $url);
            }
        }

        return $url;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function has($name)
    {
        return isset($this->urls[$name]);
    }
}
