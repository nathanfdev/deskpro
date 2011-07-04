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

		if ($is_html) {
			$info_block = strip_tags($info_block);
		}

		$info = array();

		$m = null;
		if (preg_match_all('#^\s*(.*?):(.*?)\s*$#', $info_block, $m)) {
			foreach ($m as $match) {
				$info[strtolower($match[1])] = $match[2];
			}
		}

		return $info;
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

		return $parts[0];
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

		return $parts[1];
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