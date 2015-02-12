<?php

namespace Application\AppBundle\Helper;

class UrlHostChecker
{
    public function isMatch($check_url, $verified_host, $verified_port)
    {
        $check_host = parse_url($check_url, PHP_URL_HOST);
        $check_port = parse_url($check_url, PHP_URL_PORT);

        if (
            null === $check_host
            && substr($check_url, 0, 1) === '/'
            && substr($check_url, 0, 2) !== '//'
        ) {
            return true; // url does not contain host info, so it is an absolute url redirect (example: "/news")
        }

        if (!$check_port) {
            $check_port = 80;
        }

        if (null !== $check_host && substr($check_host, 0, 1) === '/') {
            return true; // url does not contain host info, so it is an absolute url redirect (example: "/news")
        }

        return $check_host === $verified_host && (int)$check_port === (int)$verified_port;
    }

    public function isMatchUrl($check_url, $verified_url)
    {
        if (!$host = parse_url($verified_url, PHP_URL_HOST)) {
            return false;
        }

        $port = parse_url($verified_url, PHP_URL_PORT);

        return $this->isMatch($check_url, $host, $port);
    }
}
