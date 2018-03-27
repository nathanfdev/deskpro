<?php

namespace DeskPRO\Bundle\AppBundle\Request;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class UrlCorrector.
 */
class UrlCorrector
{
    const CORRECTION_INDEX_SEGMENT = 'index_segment';
    const CORRECTION_HTTPS         = 'https';
    const CORRECTION_HOST          = 'host';

    /**
     * @var array
     */
    private $options;

    /**
     * UrlCorrector constructor.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = array_merge([
            'autoCorrectScheme' => false,
            'autoCorrectHost'   => false,
            'helpdeskUrl'       => 'http://localhost/',
        ], $options);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Given a request, get an array of corrections required.
     *
     * @param Request $request
     *
     * @return array
     */
    public function getCorrections(Request $request)
    {
        $corrections = [];

        if (strpos($request->getRequestUri(), '/index.php') !== false) {
            $corrections[] = self::CORRECTION_INDEX_SEGMENT;
        }

        if ($this->options['helpdeskUrl'] && ($this->options['autoCorrectScheme'] || $this->options['autoCorrectHost'])) {
            $correctInfo   = @parse_url($this->options['helpdeskUrl']);
            $correctScheme = strtolower(@$correctInfo['scheme']);
            $correctHost   = strtolower(@$correctInfo['host']);
            $correctPort   = intval(@$correctInfo['port']) ?: ($correctInfo['scheme'] === 'https' ? 443 : 80);

            if ($correctInfo) {
                if ($this->options['autoCorrectScheme']) {
                    // We only ever 'upgrade' from http->https
                    if ($correctScheme === 'https' && $request->getScheme() !== 'https') {
                        $corrections[] = self::CORRECTION_HTTPS;
                    }
                }

                if ($this->options['autoCorrectHost']) {
                    $gotHost = $request->getHost();
                    $gotPort = intval($request->getPort()) ?: ($request->isSecure() ? 443 : 80);

                    if ($correctHost !== $gotHost) {
                        $corrections[] = self::CORRECTION_HOST;
                    } else {
                        // We consider the port part of the host if its not default 80/443
                        // (which would be caught by schema correction above)
                        if (($correctPort !== 80 && $correctPort !== 443) || ($gotPort !== 80 && $gotPort !== 443)) {
                            if ($correctPort !== $gotPort) {
                                $corrections[] = self::CORRECTION_HOST;
                            }
                        }
                    }
                }
            }
        }

        return $corrections;
    }

    /**
     * @param string  $url
     * @param Request $request
     *
     * @return string
     */
    public function correctUrlScheme($url, Request $request)
    {
        // if we are on https but the url is set to just http, we wont change it
        // (i.e., allow a manual "upgrade" to https)
        if ($request->isSecure() && !preg_match('#^https:#i', $this->options['helpdeskUrl'])) {
            $url = preg_replace('#^http:#i', 'https:', $url);
        }

        return $url;
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    public function getCorrectedHelpdeskUrl(Request $request)
    {
        return $this->correctUrlScheme(rtrim($this->options['helpdeskUrl'], '/'), $request);
    }

    /**
     * Get the real URL for the current request. Note that this does not apply
     * specific corrections; it simply returns the real, expected URL.
     *
     * @param Request $request
     *
     * @return string
     */
    public function getCorrectedUrl(Request $request)
    {
        if (null !== $qs = $request->getQueryString()) {
            $qs = '?'.$qs;
        }

        $url = $this->getCorrectedHelpdeskUrl($request).$request->getPathInfo().$qs;

        return $url;
    }
}
