<?php

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
    public $authservId = '';

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
        // Received-SPF can contain just 'fail'
        if ($this->spf === 'fail') {
            return self::SPF_HARDFAIL;
        }

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

    public static function parseReceivedSpf($value)
    {
        $authenticationResults = new self();
        if (preg_match('/^([a-z]+)(\s+\(([^:]+):)?/', $value, $matches)) {
            $authenticationResults->spf = $matches[1];
            if (!empty($matches[3])) {
                $authenticationResults->authservId = $matches[3];
            } else {
                $authenticationResults->authservId = 'Received_SPF';
            }
        }

        return $authenticationResults;
    }
}
