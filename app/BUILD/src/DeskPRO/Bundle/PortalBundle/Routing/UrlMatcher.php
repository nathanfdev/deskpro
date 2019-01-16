<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Routing;

use Orb\Util\Strings;

class UrlMatcher
{
    public function extractLanguageCode($pathinfo)
    {
        $return = [
            'lang_url_code'      => null,
            'remaining_pathinfo' => $pathinfo,
        ];

        $locale = $this->getLocale($pathinfo);

        if ($locale && 'kb' !== $locale && 'dp' !== $locale) {
            $return['lang_url_code']      = $locale;
            $return['remaining_pathinfo'] = preg_replace('#^/(.*?)(/|$)#', '/', $pathinfo);

            return $return;
        }

        $locale = $this->getLocale($pathinfo);
        if ($locale && 'kb' !== $locale && 'dp' !== $locale) {
            $return['lang_url_code']      = $locale;
            $return['remaining_pathinfo'] = '/';
        }

        return $return;
    }

    /**
     * @param string $pathinfo
     */
    private function getLocale($pathinfo)
    {
        $locale = Strings::extractRegexMatch('#^/([a-z]{2})(/|$)#', $pathinfo, 1);
        if (!$locale) {
            $locale = Strings::extractRegexMatch('#^/([a-z]{2}(_|-)[A-Z0-9]{2})(/|$)#', $pathinfo, 1);
        }

        return $locale ?: null;
    }
}
