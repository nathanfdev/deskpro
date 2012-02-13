<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
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
	 * Get info from the quote block
	 *
	 * @param string $body
	 * @param bool $is_html
	 * @return string
	 */
	public function getQuoteInfo($body, $is_html = false)
	{
		// TODO: Implement getQuoteInfo() method.
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
		// TODO: Implement cutQuoteBlock() method.
	}

	/**
	 * Get the full quote block
	 *
	 * @param string $body
	 * @param bool $is_html
	 * @return string
	 */
	public function getQuoteBlock($body, $is_html = false)
	{
		// TODO: Implement getQuoteBlock() method.
	}

	/**
	 * Get the quoted message
	 *
	 * @param string $body
	 * @param bool $is_html
	 * @return string
	 */
	public function getQuotedMessage($body, $is_html = false)
	{
		// TODO: Implement getQuotedMessage() method.
	}
}