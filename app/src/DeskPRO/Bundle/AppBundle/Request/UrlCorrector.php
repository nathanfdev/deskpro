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

namespace DeskPRO\Bundle\AppBundle\Request;

use Symfony\Component\HttpFoundation\Request;

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
            $correct_info = @parse_url($this->options['helpdeskUrl']);

            if ($correct_info) {
                if ($this->options['autoCorrectScheme']) {
                    $correct_scheme = strtolower(@$correct_info['scheme']);

                    // We only ever 'upgrade' from http->https
                    if ($correct_scheme === 'https' && $request->getScheme() !== 'https') {
                        $corrections[] = self::CORRECTION_HTTPS;
                    }
                }

                if ($this->options['autoCorrectHost']) {
                    $correct_host = strtolower(@$correct_info['host']);
                    if ($correct_host !== $request->getHost()) {
                        $corrections[] = self::CORRECTION_HOST;
                    }
                }
            }
        }

        return $corrections;
    }

    /**
     * Get the real URL for the current request. Note that this does not apply
     * specific corrections; it simply returns the real, expected URL.
     *
     * @param Request $request
     * @param array   $corrections
     *
     * @return string
     */
    public function getCorrectedUrl(Request $request)
    {
        if (null !== $qs = $request->getQueryString()) {
            $qs = '?'.$qs;
        }

        $url = rtrim($this->options['helpdeskUrl'], '/').$request->getPathInfo().$qs;

        // if we are on https but the url is set to just http, we wont change it
        // (i.e., allow a manual "upgrade" to https)
        if ($request->isSecure() && !preg_match('#^https:#i', $this->options['helpdeskUrl'])) {
            $url = preg_replace('#^http:#i', 'https:', $url);
        }

        return $url;
    }
}
