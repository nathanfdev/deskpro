<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Cutter\TextPatternCutter;

class TextMatcher
{
    const CUT_MARK = '<___DP_EMAIL_CUT_MARK___>';

    /**
     * @var string
     */
    protected $body;

    /**
     * @var \Application\DeskPRO\EmailGateway\Cutter\TextPatternCutter\TextPattern
     */
    protected $pattern;

    /**
     * @var array
     */
    protected $pattern_match;

    /**
     * @var string
     */
    protected $matched_text = '';

    /**
     * @var array
     */
    protected $matched_patterns;

    /**
     * @var string
     */
    protected $marked_body;

    /**
     * @var string
     */
    protected $mark_id;

    /**
     * @param string             $body
     * @param string|TextPattern $pattern
     */
    public function __construct($body, $pattern)
    {
        $this->body = $body;

        if (is_string($pattern)) {
            $pattern = new TextPattern($pattern);
        }

        $this->pattern = $pattern;
    }

    /**
     * Given a tokenized pattern, process it against the body to find matching results.
     */
    public function process()
    {
        if ($this->marked_body !== null) {
            return;
        }

        $this->pattern_match = false;
        $this->marked_body   = $this->body;
        $m                   = null;

        if (preg_match($this->pattern->getPattern(), $this->body, $m)) {
            $this->matched_patterns = $m;
            $this->matched_text     = $m[0];
            $this->marked_body      = preg_replace($this->pattern->getPattern(), self::CUT_MARK.'$0', $this->body);
            $this->pattern_match    = true;
        }
    }

    /**
     * Does the pattern match?
     *
     * @return bool
     */
    public function isMatch()
    {
        $this->process();
        if ($this->pattern_match) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getMatchedText()
    {
        return $this->matched_text;
    }

    /**
     * @param string|int $k The offset in the matches array
     *
     * @return string|null
     */
    public function getMatchedPattern($k)
    {
        return isset($this->matched_patterns[$k]) ? $this->matched_patterns[$k] : null;
    }

    /**
     * Process the pattern and if it matches, mark the beginning of the cut areas with self::CUT_MARK.
     *
     * @return string
     */
    public function getMarkedDocument()
    {
        $this->process();

        return $this->marked_body;
    }

    /**
     * Cut at the first cut mark.
     *
     * @param string $mark_string
     *
     * @return string
     */
    public function getCutBody()
    {
        $body = $this->getMarkedDocument();

        $pos = strpos($body, self::CUT_MARK);
        if ($pos === false) {
            return $body;
        }

        return substr($body, 0, $pos);
    }
}
