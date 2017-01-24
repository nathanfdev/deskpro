<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace Application\DeskPRO\EmailGateway\Reader\Item;

class AuthenticationResults
{
    const SPF_NONE      = 'none';
    const SPF_NEUTRAL   = 'neutral';
    const SPF_PASS      = 'pass';
    const SPF_POLICY    = 'policy';
    const SPF_HARDFAIL  = 'hardfail';
    const SPF_SOFTFAIL  = 'softfail';
    const SPF_TEMPERROR = 'temperror';
    const SPF_PERMERROR = 'permerror';

    const DKIM_NONE      = 'none';
    const DKIM_PASS      = 'pass';
    const DKIM_UNKNOWN   = 'unknown';
    const DKIM_SIGNED    = 'signed';
    const DKIM_FAIL      = 'fail';
    const DKIM_DISCARD   = 'discard';
    const DKIM_NXDOMAIN  = 'nxdomain';
    const DKIM_TEMPERROR = 'temperror';
    const DKIM_PERMERROR = 'permerror';

    /**
     * @var string
     */
    public $authservId;

    /**
     * @var string|null
     */
    public $spf = null;

    /**
     * @var string|null
     */
    public $dkim = null;

    /**
     * @return string
     */
    public function getAuthservId()
    {
        return $this->authservId;
    }

    /**
     * @return null|string
     */
    public function getSpfResult()
    {
        return $this->spf;
    }

    /**
     * @return null|string
     */
    public function getDkimResult()
    {
        return $this->dkim;
    }

    /**
     * @param $header
     *
     * @return AuthenticationResults
     */
    public static function parseHeader($header)
    {
        $authenticationResults = new self();

        if (preg_match('|^\s*([^;]+);|', $header, $matches)) {
            $authenticationResults->authservId = $matches[1];
            $header                            = substr($header, strlen($matches[0]));
        } else {
            return null;
        }

        $header = preg_replace('|\([^)+]+\)|', '', $header);

        if (preg_match_all('/(dkim|spf)=[a-z]+/', $header, $matches)) {
            foreach ($matches[0] as $match) {
                list($key, $value)           = explode('=', $match);
                $authenticationResults->$key = $value;
            }
        }

        return $authenticationResults;
    }
}
