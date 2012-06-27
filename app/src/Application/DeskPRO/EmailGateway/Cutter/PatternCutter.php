<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

use Application\DeskPRO\EmailGateway\Cutter\PatternCutter\HtmlPattern;
use Application\DeskPRO\EmailGateway\Cutter\PatternCutter\HtmlMatcher;
use Application\DeskPRO\EmailGateway\Cutter\Def\QuoteDef;

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
	 * @param \Application\DeskPRO\EmailGateway\Cutter\PatternCutter\HtmlPattern|string $pattern
	 */
	public function addPattern($pattern)
	{
		if (is_string($pattern)) {
			$pattern = new HtmlPattern($pattern);
		}

		$this->patterns[] = $pattern;
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
	 * @param string $body
	 * @param bool $is_html
	 * @return string
	 */
	public function cutQuoteBlock($body, $is_html = false)
	{
		if (!$is_html) {
			return $body;
		}

		foreach ($this->patterns as $pattern) {
			$matcher = new HtmlMatcher($body, $pattern);
			if ($matcher->isMatch()) {
				$this->matched_patterns[] = $pattern;
				$body = $matcher->getMarkedDocument();
			}
		}

		$pos = strpos($body, HtmlMatcher::CUT_MARK);
		if ($pos !== false) {
			$body = substr($body, 0, $pos);
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
	 * @return PatternCutter\HtmlPattern|null
	 */
	public function getMatchedPatterns()
	{
		return $this->matched_patterns;
	}
}