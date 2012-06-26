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

namespace Application\DeskPRO\EmailGateway\Cutter\PatternCutter;

class HtmlMatcher
{
	const CUT_MARK = '<!-- DP_EMAIL_CUT_MARK -->';

	/**
	 * @var string
	 */
	protected $body;

	/**
	 * @var \Application\DeskPRO\EmailGateway\Cutter\PatternCutter\HtmlPattern
	 */
	protected $pattern;

	/**
	 * @var \QueryPath\DOMQuery
	 */
	protected $qp;

	/**
	 * @var array
	 */
	protected $pattern_matches;

	/**
	 * @var string
	 */
	protected $marked_body;


	/**
	 * @param string $body
	 * @param string|HtmlPattern $pattern
	 */
	public function __construct($body, $pattern)
	{
		$this->body = $body;

		if (is_string($pattern)) {
			$pattern = new HtmlPattern($pattern);
		}

		$this->pattern = $pattern;
	}


	/**
	 * Given a tokenized pattern, process it against the body to find matching results
	 *
	 * @return array
	 */
	public function process()
	{
		if ($this->pattern_matches !== null) {
			return $this->pattern_matches;
		}

		$tokens = $this->pattern->getTokens();

		$results = array($this->getQpBranch());
		while ($tokens && count($results)) {
			$results = $this->consumeNavigates($results, $tokens);
			$results = $this->consumeMatches($results, $tokens);
		}

		$this->pattern_matches = $results;

		return $this->pattern_matches;
	}


	/**
	 * Does the pattern match?
	 *
	 * @return bool
	 */
	public function doesMatch()
	{
		$this->process();
		if ($this->pattern_matches) {
			return true;
		}

		return false;
	}


	/**
	 * Process the pattern and if it matches, mark the beginning of the cut areas with self::CUT_MARK
	 *
	 * @param string|HtmlPattern $pattern
	 * @param string $mark_string
	 * @return string
	 */
	public function getMarkedDocument()
	{
		if ($this->marked_body) {
			return $this->marked_body;
		}

		$results = $this->process();
		if (!$results) {
			return $this->body;
		}

		foreach ($results as $res) {
			$res->before(self::CUT_MARK);
		}

		ob_start();
		$this->getQp()->writeXHTML();
		$this->marked_body = ob_get_clean();

		return $this->marked_body;
	}


	/**
	 * Cut at the first cut mark
	 *
	 * @param string $mark_string
	 * @return string
	 */
	public function getCutBody()
	{
		$body = $this->getMarkedDocument(self::CUT_MARK);

		$pos = strpos($body, self::CUT_MARK);
		if ($pos === false) {
			return $body;
		}

		return substr($body, 0, $pos);
	}


	/**
	 * Consume  all navigate finds and return a new array of branches that match
	 *
	 * @param array $results
	 * @param array $tokens
	 * @return array
	 */
	public function consumeNavigates(array $results, array &$tokens)
	{
		$new_results = $results;

		while ($token = array_shift($tokens)) {

			// Next token isnt a nav
			if ($token[0] != 'nav') {
				array_unshift($tokens, $token);
				break;
			}

			// Get rid of token type on the array
			array_shift($token);

			$new_results = array();

			foreach ($results as $branch) {
				if ($token[0] == ':parent') {
					$branch->parent();
					if ($branch->length) {
						$new_results[] = $branch;
					}
				} else {
					foreach ($token as $sel) {
						if ($sel === null) {
							$new_results[] = $branch;
						} else {
							$try_branch = $branch->branch();
							$try_branch->find($sel);

							if ($try_branch->length) {
								$new_results[] = $try_branch;
							}
						}
					}
				}
			}

			$results = $new_results;
		}

		return $new_results;
	}


	/**
	 * Process all match requirements on the result set and return a new array of branches that match.
	 *
	 * @param array $results
	 * @param array $tokens
	 * @return array
	 */
	public function consumeMatches(array $results, array &$tokens)
	{
		$new_results = $results;

		while ($token = array_shift($tokens)) {

			// Next token isnt a match
			if ($token[0] != 'match') {
				array_unshift($tokens, $token);
				break;
			}

			// Get rid of token type on the array
			array_shift($token);

			$new_results = array();

			foreach ($results as $branch) {
				$text = $branch->text();
				if (preg_match($token[0], $text)) {
					$new_results[] = $branch;
				}
			}

			$results = $new_results;
		}

		return $new_results;
	}


	/**
	 * @return \QueryPath\DOMQuery
	 */
	public function getQp()
	{
		if ($this->qp) {
			return $this->qp;
		}

		$this->qp = \QueryPath::withHTML($this->body, 'body', array('convert_to_encoding' => null));

		return $this->qp;
	}


	/**
	 * @return \QueryPath\DOMQuery
	 */
	public function getQpBranch()
	{
		return $this->getQp()->branch();
	}
}