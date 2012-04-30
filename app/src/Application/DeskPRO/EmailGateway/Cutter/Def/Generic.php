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

namespace Application\DeskPRO\EmailGateway\Cutter\Def;

use Orb\Util\Strings;

class Generic implements ForwardDef, QuoteDef
{


	/**
	 * Get an array of info from the forwarded block
	 *
	 * @param string $body
	 * @param bool $is_html
	 * @return array
	 */
	public function getForwardInfo($body, $is_html = false)
	{
		$parts = $this->_splitForwardedBlock($body, $is_html);
		if (!$parts) return null;

		$info_block = $parts[0];

		return self::parseForwardHeaders($info_block, $is_html);
	}

	protected function _splitForwardedBlock($body, $is_html)
	{
		$block = $this->getForwardBlock($body, $is_html);
		if (!$block) return null;

		if ($is_html) {
			$block = str_replace(array('<br />', '<br/>'), '<br>', $block);
			$parts = preg_split('#<br>\s*<br>#i', $block, 2);
		} else {
			$parts = preg_split('#(\n|\r\n){2}#i', $block, 2);
		}

		if (count($parts) != 2) {
			return null;
		}

		return $parts;
	}

	/**
	 * Cut out the forward block from the body
	 *
	 * @param string $body
	 * @param bool $is_html
	 * @return string
	 */
	public function cutForwardBlock($body, $is_html = false)
	{
		$parts = preg_split('#-{3,15}\s*Forward(ed)?( Message)?\s*-{3,15}#i', $body, 2);
		if (count($parts) != 2) {
			return null;
		}

		$new_body = trim($parts[0]);

		if ($is_html) {
			$new_body = Strings::trimHtml($new_body);
		}

		return $new_body;
	}

	/**
	 * Get the full forward block
	 *
	 * @param string $body
	 * @param bool $is_html
	 * @return string
	 */
	public function getForwardBlock($body, $is_html = false)
	{
		$parts = preg_split('#-{3,15}\s*Forward(ed)?( Message)?\s*-{3,15}#i', $body, 2);
		if (count($parts) != 2) {
			return null;
		}

		return $parts[1];
	}

	/**
	 * Get the forwarded message (miunus the header forwarded block);
	 *
	 * @param string $body
	 * @param bool $is_html
	 * @return string
	 */
	public function getForwardedMessage($body, $is_html = false)
	{
		$parts = $this->_splitForwardedBlock($body, $is_html);
		if (!$parts) return null;

		$msg = trim($parts[1]);

		if ($is_html) {
			$msg = Strings::trimHtml($msg);
		}

		return $msg;
	}

	/**
	 * Get an array of info based off of text found in quoted/forwarded block. Things like
	 * From: and To: address.
	 *
	 * @param $info_block
	 * @param $is_html
	 * @return array
	 */
	public static function parseForwardHeaders($info_block, $is_html)
	{
		if ($is_html) {
			$info_block = str_replace(array('<br>', '<br/>', '<br />', '<p>', '</p>'), "\n", $info_block);
			$info_block = strip_tags($info_block);

			$info_block = html_entity_decode($info_block);
		}

		$info = array();

		$m = null;
		if (preg_match_all('#^(.*?):(.*?)$#m', $info_block, $m, PREG_SET_ORDER)) {
			foreach ($m as $match) {
				$info[strtolower(trim($match[1]))] = trim($match[2]);
			}
		}

		if (isset($info['from'])) {
			$info['from_email'] = '';
			$info['from_name'] = '';

			if (function_exists('imap_rfc822_parse_adrlist')) {
				$from_addr = imap_rfc822_parse_adrlist($info['from'], 'null');
				if ($from_addr && count($from_addr)) {
					$from_addr = array_pop($from_addr);
					$info['from_email'] = $from_addr->mailbox .'@'. $from_addr->host;
					if (!empty($from_addr->name)) {
						$info['from_name'] = $from_addr->name;
					}
				}
			} else {
				if (strpos($info['from'], '<') !== false) {
					$info['from_email'] = Strings::extractRegexMatch('#<(.*?)>#', $info['from'], 1);
				} else {
					$info['from_email'] = $info['from'];
				}
			}
		}

		if (isset($info['to'])) {
			$info['to_email'] = '';
			$info['to_name'] = '';

			if (function_exists('imap_rfc822_parse_adrlist')) {
				$to_addr = imap_rfc822_parse_adrlist($info['to'], 'null');
				if ($to_addr && count($to_addr)) {
					$to_addr = array_pop($to_addr);
					$info['to_email'] = $to_addr->mailbox .'@'. $to_addr->host;
					if (!empty($to_addr->name)) {
						$info['to_name'] = $to_addr->name;
					}
				}
			} else {
				if (strpos($info['to'], '<') !== false) {
					$info['to_email'] = Strings::extractRegexMatch('#<(.*?)>#', $info['to'], 1);
				} else {
					$info['to_email'] = $info['to'];
				}
			}
		}

		return $info;
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
		if (($pos = strpos($body, '<!--DP_TOP_MARK-->')) === false) {
			if (($pos = strpos($body, '_______________________.')) === false) {
				return $body;
			}
		}

		$body = substr($body, 0, $pos);

		// We try to cut known markers only when the top marker was successfully found
		$body = $this->cutKnownQuoteMarkers($body, $is_html);

		return $body;
	}


	/**
	 * @param $body
	 * @param $is_html
	 */
	public function cutKnownQuoteMarkers($body, $is_html)
	{
		// On Wednesday, 25 April 2012 at 17:18, John Doe wrote:
		$parts = preg_split('#(<p>)?\s*On [a-zA-Z]+, [0-9]+ [a-zA-Z]+? [0-9]+ at [0-9]+:[0-9]+\s*(AM|am|PM|pm)?, .*? wrote:\s*(</p>)?#', $body, 2);
		if (isset($parts[1])) {
			return $parts[0];
		}

		// On 24/04/2012 17:12, John Doe wrote:
		$parts = preg_split('#(<p>)?\s*On [0-9]{2}(/|\-)[0-9]{2}(/|\-)[0-9]{2,4} [0-9]{1,2}:[0-9]{1,2}( AM| am| PM| pm)?, .*? wrote:\s*(</p>)?#', $body, 2);
		if (isset($parts[1])) {
			return $parts[0];
		}

		// -----Original Message-----
		// From: John Doe
		$parts = preg_split("#(>*\s*)?-----Original Message-----\n(>*\s*)?From:\s", $body, 2);
		if (isset($parts[1])) {
			return $parts[0];
		}

		// From: John Doe
		// Sent: 30 April 2012 08:24
		// To: Jane Doe
		// Subject: ABC
		if (!$is_html) {
			$parts = preg_split("#From: .*?\nSent: .*?\nTo: .*?\nSubject: .*?#", $body, 2);
			if (isset($parts[1])) {
				return $parts[0];
			}
		} else {
			// Standard formatting with outlook
			$parts = preg_split("#<b><span.*?>From:\s*</span></b>\s*<span.*?>.*?</span>\s*(<br>|<br/>|<br />)\n?<b><span.*?>Sent:\s*</span></b>\s*<span.*?>.*?</span>\s*(<br>|<br/>|<br />)\n?<b><span.*?>To:\s*</span></b>\s*<span.*?>.*?</span>\s*(<br>|<br/>|<br />)\n?<b><span.*?>Subject:\s*</span></b>\s*<span.*?>.*?</span>\s*(<br>|<br/>|<br />)#", $body, 2);
			if (isset($parts[1])) {
				return $parts[0];
			}
		}

		return $body;
	}
}
