<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\DeskPRO\EmailGateway\Cutter;

use Application\DeskPRO\EmailGateway\Cutter\Def\QuoteDef;
use Application\DeskPRO\EmailGateway\Cutter\PatternCutter\HtmlMatcher;
use Application\DeskPRO\EmailGateway\Cutter\PatternCutter\HtmlPattern;
use Orb\Util\Strings;

class PatternCutter implements QuoteDef
{
    /**
     * @var \Application\DeskPRO\EmailGateway\Cutter\PatternCutter\HtmlPattern[]
     */
    protected $patterns = array();

    /**
     * @var PatternCutter\HtmlPattern[]
     */
    protected $matched_patterns;

    /**
     * @var array
     */
    protected $translate_map;

    /**
     * @var int
     */
    protected $limit = 0;

    /**
     * @var int
     */
    protected $max_lines_from_end = 0;

    /**
     * @var array
     */
    private $require_from = array();

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
     * Sets how many lines from the end of the document a section can be before it is not considered.
     *
     * Note: This is imprecise because this is an HTML email we are processing. A rendered line doesnt
     * always translate well to a text line, though we try to normalise this the best we can.
     *
     * But since it's not a perfect system, it's best to pad the value a bit. E.g., if ideally you want
     * no more than 10 lines, then try padding that to 15 to account for "extra" newlines that might
     * get added from our dumb html-to-text line counter.
     *
     * @param int $max
     */
    public function setMaxLinesFromEnd($max)
    {
        $this->max_lines_from_end = $max;
    }

    /**
     * Sets which email addresses must match in a matched pattern for the pattern to really match.
     * If none of these email addresses exist in the match text, then the pattern is not considered a match.
     *
     * Note: Again, this is imprecise because the matcher is a DOM walker and we don't always know what line
     * a from address is on. So this is just checking for an email address "around" the point at which
     * the marker was found (within 10 lines of it).
     *
     * @param array $require_from
     */
    public function setRequireFrom(array $require_from)
    {
        $this->require_from = $require_from;
    }

    /**
     * @param array $translate_map
     */
    public function setTranslateMap(array $translate_map)
    {
        $this->translate_map = $translate_map;
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
     * @param \Application\DeskPRO\EmailGateway\Cutter\PatternCutter\HtmlPattern|string $pattern
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

                    $pattern          = new HtmlPattern($pattern);
                    $this->patterns[] = $pattern;
                }
            } else {
                $pattern          = new HtmlPattern($pattern);
                $this->patterns[] = $pattern;
            }
        } else {
            $this->patterns[] = $pattern;
        }
    }

    /**
     * Add an array of patterns
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
     * Cut out the quote block
     *
     * @param  string $body
     * @param  bool   $is_html
     * @return string
     */
    public function cutQuoteBlock($body, $is_html = false)
    {
        if (!$is_html) {
            return $body;
        }

        $body = str_replace('<br></br>', '<br />', $body);
        $body = str_replace('<br>', '<br />', $body);

        foreach ($this->patterns as $pattern) {
            $matcher = new HtmlMatcher($body, $pattern);
            if ($matcher->isMatch()) {
                $this->matched_patterns[] = $pattern;
                $body                     = $matcher->getMarkedDocument();
            }
        }

        // Limiting how many we are trimming from the end
        if ($this->limit) {
            $parts = explode(HtmlMatcher::CUT_MARK, $body);
            if (count($parts) > 1) {
                $do_pop = true;
                $last   = $parts[count($parts) - 1];
                $last   = trim(Strings::html2Text($last));

                // We want to verify its at the end
                if ($this->max_lines_from_end) {
                    $do_pop = false;
                    if (substr_count($last, "\n") < $this->max_lines_from_end) {
                        $do_pop = true;
                    }
                }

                if ($do_pop && $this->require_from) {
                    $do_pop = false;
                    $lines  = explode("\n", $last);
                    $lines  = array_slice($lines, 0, 10);
                    $lines  = implode("\n", $lines);
                    $lines  = strtolower($lines);
                    foreach ($this->require_from as $from) {
                        $from = strtolower($from);
                        if (strpos($lines, $from) !== false) {
                            $do_pop = true;
                            break;
                        }
                    }
                }

                if ($do_pop) {
                    array_pop($parts);
                    $body = implode(HtmlMatcher::CUT_MARK, $parts);
                }
            }

            $body = str_replace(HtmlMatcher::CUT_MARK, '', $body);

        // No limit, strip from the first (top) one
        } else {
            $pos = strpos($body, HtmlMatcher::CUT_MARK);
            if ($pos !== false) {
                $body = substr($body, 0, $pos);
            }
        }

        return $body;
    }

    /**
     * @param $body
     * @return PatternCutter\HtmlMatcher|null
     */
    public function findMatchingMatcher($body)
    {
        $last_qp = null;
        foreach ($this->patterns as $pattern) {
            $matcher = new HtmlMatcher($body, $pattern);
            if ($last_qp) {
                $last_qp->top();
                $matcher->_setQp($last_qp);
            }

            if ($matcher->isMatch()) {
                $this->matched_patterns[] = $pattern;

                return $matcher;
            }

            $last_qp = $matcher->getQp();
        }

        return null;
    }

    /**
     * @return PatternCutter\HtmlPattern[]
     */
    public function getMatchedPatterns()
    {
        return $this->matched_patterns;
    }
}
