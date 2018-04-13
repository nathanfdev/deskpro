<?php

namespace DeskPRO\Bundle\AppBundle\Helper;

/**
 * This util is used to determine if two URLs are the same host. This is used
 * in our redirection listener to verify a redirect is safe by making sure
 * the target URL is the same host as the helpdesk.
 */
class UrlHostChecker
{
    /**
     * @param string $checkUrl
     * @param string $verifiedHost
     * @param string $verifiedPort
     * @param bool   $allowSchemeSwitch If the ports are standard (80/443), this allows the reverse.
     *                                  E.g. consider host:80 a match to host:443
     *
     * @return bool
     */
    public function isMatch($checkUrl, $verifiedHost, $verifiedPort, $allowSchemeSwitch = true)
    {
        // no schemaless urls
        if (substr($checkUrl, 0, 2) === '//') {
            return false;
        }

        $parsed = parse_url($checkUrl);

        $checkScheme = @$parsed['scheme'] ?: 'http';
        $checkHost   = @$parsed['host'];
        $checkPort   = @$parsed['port'];
        if (!$checkPort) {
            $checkPort = $checkScheme === 'https' ? 443 : 80;
        }

        if (
            null === $checkHost
            && substr($checkUrl, 0, 1) === '/'
        ) {
            return true; // url does not contain host info, so it is an absolute url redirect (example: "/news")
        }

        if (null !== $checkHost && substr($checkHost, 0, 1) === '/') {
            return true; // url does not contain host info, so it is an absolute url redirect (example: "/news")
        }

        if ($checkHost === $verifiedHost && (int) $checkPort === (int) $verifiedPort) {
            return true;
        }

        // Dont care abou tports if $allowSchemeSwitch and ports are standard
        if ($allowSchemeSwitch && in_array($checkPort, [80, 443]) && in_array($verifiedPort, [80, 443])) {
            return $checkHost === $verifiedHost;
        }

        return false;
    }

    /**
     * @param string $checkUrl
     * @param string $verifiedUrl
     * @param bool   $allowSchemeSwitch
     *
     * @return bool
     */
    public function isMatchUrl($checkUrl, $verifiedUrl, $allowSchemeSwitch = true)
    {
        if ($checkUrl === $verifiedUrl) {
            return true;
        }

        $parsed = parse_url($verifiedUrl);
        $scheme = @$parsed['scheme'] ?: 'http';

        if (!$host = @$parsed['host']) {
            return false;
        }

        if (!$port = @$parsed['port']) {
            $port = $scheme === 'https' ? 443 : 80;
        }

        return $this->isMatch($checkUrl, $host, $port, $allowSchemeSwitch);
    }

    /**
     * @param string $url
     * @param bool   $withScheme
     * @param bool   $stripWww
     * @param bool   $keepPath
     *
     * @return string
     */
    public function simplifyUrl($url, $withScheme = false, $stripWww = true, $keepPath = false)
    {
        // fix schemaless urls
        if (substr($url, 0, 2) != '//' && !preg_match('#^\w+://#', $url)) {
            $url = '//'.$url;
        }

        $parsed = parse_url($url);
        $scheme = @$parsed['scheme'] ?: 'http';
        if (!in_array($scheme, ['http', 'https'])) {
            $scheme = 'http';
        }

        $host = @$parsed['host'];
        if ($stripWww) {
            $host = preg_replace('/^www\./', '', $host);
        }

        $port = @$parsed['port'];

        if (!$host) {
            return '';
        }

        $result = '';
        if ($withScheme) {
            $result .= $scheme.'://';
        }

        $result .= $host;
        $result .= ($port && $port != 80) ? ':'.$port : '';
        if ($keepPath && isset($parsed['path'])) {
            $result .= $parsed['path'];
        }

        return $result;
    }
}
