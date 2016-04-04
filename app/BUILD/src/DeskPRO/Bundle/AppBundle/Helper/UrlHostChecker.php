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

namespace DeskPRO\Bundle\AppBundle\Helper;

class UrlHostChecker
{
    public function isMatch($check_url, $verified_host, $verified_port)
    {
        // no schemaless urls
        if (substr($check_url, 0, 2) == '//') {
            return false;
        }

        $check_scheme = parse_url($check_url, PHP_URL_SCHEME) ?: 'http';
        $check_host   = parse_url($check_url, PHP_URL_HOST);
        $check_port   = parse_url($check_url, PHP_URL_PORT) ?: 80;
        if (!$check_port) {
            $check_port = $check_scheme === 'https' ? 443 : 80;
        }

        if (
            null === $check_host
            && substr($check_url, 0, 1) === '/'
        ) {
            return true; // url does not contain host info, so it is an absolute url redirect (example: "/news")
        }

        if (null !== $check_host && substr($check_host, 0, 1) === '/') {
            return true; // url does not contain host info, so it is an absolute url redirect (example: "/news")
        }

        return $check_host === $verified_host && (int) $check_port === (int) $verified_port;
    }

    public function isMatchUrl($check_url, $verified_url)
    {
        if ($check_url === $verified_url) {
            return true;
        }

        $scheme = parse_url($verified_url, PHP_URL_SCHEME) ?: 'http';

        if (!$host = parse_url($verified_url, PHP_URL_HOST)) {
            return false;
        }

        $port = parse_url($verified_url, PHP_URL_PORT);
        if (!$port) {
            $port = $scheme === 'https' ? 443 : 80;
        }

        return $this->isMatch($check_url, $host, $port);
    }
}
