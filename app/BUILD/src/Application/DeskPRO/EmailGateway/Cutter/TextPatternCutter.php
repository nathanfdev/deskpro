<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EmailGateway\Cutter;

use Application\DeskPRO\EmailGateway\Cutter\Def\QuoteDef;
use Application\DeskPRO\EmailGateway\Cutter\TextPatternCutter\TextMatcher;
use Application\DeskPRO\EmailGateway\Cutter\TextPatternCutter\TextPattern;

class TextPatternCutter implements QuoteDef
{
    /**
     * @var \Application\DeskPRO\EmailGateway\Cutter\TextPatternCutter\TextPattern[]
     */
    protected $patterns = [];

    /**
     * @var TextPatternCutter\TextPattern[]
     */
    protected $matched_patterns;

    /**
     * @var array
     */
    protected $translate_map;

    /**
     * @var array
     */
    private $require_from = [];

    /**
     * @var int
     */
    protected $limit = 0;

    /**
     * @param array $translate_map
     */
    public function setTranslateMap(array $translate_map)
    {
        $this->translate_map = $translate_map;
    }

    /**
     * Sets which email addresses must match in a matched pattern for the pattern to really match.
     * If none of these email addresses exist in the match text, then the pattern is not considered a match.
     *
     * @param array $require_from
     */
    public function setRequireFrom(array $require_from)
    {
        $this->require_from = $require_from;
    }

    /**
     * How many quotes to remove (counts from bottom). 0 is unlimited.
     *
     * @param string $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return array
     */
    public function getTranslateMap()
    {
        if (!$this->translate_map) {
            $this->translate_map = new \Application\DeskPRO\Config\UserFileConfig('cut-patterns-translate');
            $this->translate_map = $this->translate_map->all();
        }

        return $this->translate_map;
    }

    /**
     * @param \Application\DeskPRO\EmailGateway\Cutter\TextPatternCutter\TextPattern|string $pattern
     */
    public function addPattern($pattern)
    {
        if (is_string($pattern)) {
            if (strpos($pattern, 'lang:') === 0) {
                $translate_map = $this->getTranslateMap();

                $orig_pattern = preg_replace('#^lang:\s*#', '', $pattern);

                foreach ($translate_map as $set) {
                    $pattern = $orig_pattern;
                    foreach ($set as $f => $r) {
                        $pattern = str_replace($f, $r, $pattern);
                    }

                    $pattern          = new TextPattern($pattern);
                    $this->patterns[] = $pattern;
                }
            } else {
                $pattern          = new TextPattern($pattern);
                $this->patterns[] = $pattern;
            }
        } else {
            $this->patterns[] = $pattern;
        }
    }

    /**
     * Add an array of patterns.
     *
     * @param array $patterns
     */
    public function addPatterns(array $patterns)
    {
        foreach ($patterns as $pattern) {
            $this->addPattern($pattern);
        }
    }

    /**
     * Cut out the quote block.
     *
     * @param string $body
     * @param bool   $is_html
     *
     * @return string
     */
    public function cutQuoteBlock($body, $is_html = false)
    {
        if ($is_html) {
            return $body;
        }

        foreach ($this->patterns as $pattern) {
            $matcher = new TextMatcher($body, $pattern);
            if ($matcher->isMatch()) {
                if ($this->require_from) {
                    $do_add = false;

                    $test_text = $matcher->getMatchedPattern('from');
                    if (!$test_text) {
                        $test_text = $matcher->getMatchedText();
                    }

                    $test_text = strtolower($test_text);

                    if (preg_match('#[^ ]@[^ ]\.[^ ]#', $test_text)) {
                        foreach ($this->require_from as $from) {
                            $from = strtolower($from);
                            if (strpos($test_text, $from) !== false) {
                                $do_add = true;
                                break;
                            }
                        }
                    } else {
                        $do_add = true;
                    }
                } else {
                    $do_add = true;
                }

                if ($do_add) {
                    $this->matched_patterns[] = $pattern;
                    $body                     = $matcher->getMarkedDocument();
                }
            }
        }

        // Limiting how many we are trimming from the end
        if ($this->limit) {
            $pos = strrpos($body, TextMatcher::CUT_MARK);
            if ($pos !== false) {
                $body = trim(substr($body, 0, $pos));
            }
            $body = str_replace(TextMatcher::CUT_MARK, '', $body);

        // No limit
        } else {
            $pos = strpos($body, TextMatcher::CUT_MARK);
            if ($pos !== false) {
                $body = trim(substr($body, 0, $pos));
            }
        }

        return $body;
    }

    /**
     * @param $body
     *
     * @return TextPatternCutter\TextPattern|null
     */
    public function findMatchingMatcher($body)
    {
        $last_qp = null;
        foreach ($this->patterns as $pattern) {
            $matcher = new TextMatcher($body, $pattern);
            if ($matcher->isMatch()) {
                $this->matched_patterns[] = $pattern;

                return $matcher;
            }
        }

        return;
    }

    /**
     * @return TextPatternCutter\TextPattern|null
     */
    public function getMatchedPatterns()
    {
        return $this->matched_patterns;
    }
}
