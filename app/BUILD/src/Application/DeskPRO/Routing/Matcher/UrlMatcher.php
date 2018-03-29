<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Routing\Matcher;

use Orb\Util\Strings;

class UrlMatcher extends \Symfony\Component\Routing\Matcher\UrlMatcher
{
    /** @var bool|null */
    protected $got_locale = null;

    public function match($pathInfo)
    {
        //------------------------------
        // We check for locale prefix in user section
        //------------------------------

        $this->got_locale = null;

        $nocheck_sections = [
            '/agent',
            '/admin',
            '/dev',
            '/api',
        ];

        $check_for_locale = true;
        foreach ($nocheck_sections as $s) {
            if (strpos($pathInfo, $s) === 0) {
                $check_for_locale = false;
            }
        }

        if ($check_for_locale) {
            $locale = Strings::extractRegexMatch('#^/([a-z]{2})/#', $pathInfo, 1);
            if ($locale) {
                $locale = Strings::extractRegexMatch('#^/([a-z]{2}_[A-Z]{2}/#', $pathInfo, 1);
            }

            if ($locale) {
                $this->got_locale = $locale;

                // Remove it from the
                $pathInfo = preg_replace('#^/(.*?)/#', '/', $pathInfo);
            }
        }

        return parent::match($pathInfo);
    }
}
