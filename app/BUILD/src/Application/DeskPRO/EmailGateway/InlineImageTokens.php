<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway;

use Application\DeskPRO\EmailGateway\Reader\AbstractReader;
use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * This goes through an email body and creates unique tokens for every place there are inline images.
 */
class InlineImageTokens
{
    /**
     * @var \Application\DeskPRO\EmailGateway\Reader\AbstractReader
     */
    protected $reader;

    /**
     * @var array
     */
    protected $tokens = [];

    public function __construct(AbstractReader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Adds tokens to $body where inline tags.
     *
     * @param string $body
     *
     * @return mixed|string
     */
    public function processTokens($body)
    {
        $haveCids = [];
        foreach ($this->reader->getAttachments() as $attach) {
            $cid = $attach->getContentId();
            if ($cid) {
                $haveCids[$cid] = true;
            }
        }

        if (!$haveCids) {
            return $body;
        }

        //------------------------------
        // HTML
        //------------------------------

        $m = null;
        if (preg_match_all('#<img[^>]*/?>(</img>)?#iu', $body, $m, \PREG_SET_ORDER)) {
            foreach ($m as $match) {
                // Check if it is even an inline image
                $cid = Strings::extractRegexMatch('#src=("|\')cid:(.*?)(\1)#iu', $match[0], 2);
                if (!$cid || !isset($haveCids[$cid])) {
                    continue;
                }

                $token = $this->generateToken();
                $body  = str_replace($match[0], $token, $body);

                if (!isset($this->tokens[$cid])) {
                    $this->tokens[$cid] = [];
                }

                $this->tokens[$cid][] = $token;
            }
        }

        //------------------------------
        // Text
        //------------------------------

        // There isn't technically a syntax for inlining images in
        // text emails, but some clients use this syntax:
        // [cid:EC8B017D-CAFD-4216-A6E3-EB8CD03AB3EA]
        // So we can try to handle them anyway

        $m = null;
        if (preg_match_all('#\[cid:(.*?)\]#iu', $body, $m, \PREG_SET_ORDER)) {
            foreach ($m as $match) {
                // Check if it is even an inline image
                $cid = $match[1];
                if (!$cid || !isset($haveCids[$cid])) {
                    continue;
                }

                $token = $this->generateToken();
                $body  = str_replace($match[0], $token, $body);

                if (!isset($this->tokens[$cid])) {
                    $this->tokens[$cid] = [];
                }

                $this->tokens[$cid][] = $token;
            }
        }

        return $body;
    }

    /**
     * Check if a content ID has a corresponding token.
     *
     * @param string $cid
     *
     * @return bool
     */
    public function hasToken($cid)
    {
        return isset($this->tokens[$cid]);
    }

    /**
     * Get the token for a content id.
     *
     * @param string $cid
     * @param bool   $first
     *
     * @return string|null
     */
    public function getToken($cid, $first = true)
    {
        if ($first) {
            return isset($this->tokens[$cid]) ? $this->tokens[$cid][0] : null;
        } else {
            return isset($this->tokens[$cid]) ? $this->tokens[$cid] : null;
        }
    }

    /**
     * @return array
     */
    public function getAllTokens()
    {
        return $this->tokens;
    }

    /**
     * Replace a content ID with something in body.
     *
     * @param string $cid
     * @param string $replacement
     * @param string $body
     *
     * @return string
     */
    public function replaceToken($cid, $replacement, $body)
    {
        $tokens = $this->getToken($cid, false);
        if (!$tokens) {
            return $body;
        }

        foreach ($tokens as $t) {
            $body = str_replace($t, $replacement, $body);
        }

        return $body;
    }

    /**
     * Count how many tokens were read.
     *
     * @return int
     */
    public function count()
    {
        return count($this->tokens);
    }

    /**
     * Get the CID's we were able to read.
     *
     * @return array
     */
    public function getCids()
    {
        return array_keys($this->tokens);
    }

    /**
     * @return string
     */
    public function generateToken()
    {
        return '__dp_'.mt_rand(1000, 9999).'_a'.count($this->tokens).'_'.Util::requestUniqueId().'__';
    }
}
