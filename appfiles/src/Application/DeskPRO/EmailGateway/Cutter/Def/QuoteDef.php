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

interface QuoteDef
{
	/**
	 * Get info from the quote block
	 * 
	 * @param string $body
	 * @param bool $is_html
	 * @return string
	 */
	public function getQuoteInfo($body, $is_html = false);

	/**
	 * Cut out the quote block
	 * 
	 * @param string $body
	 * @param bool $is_html
	 * @return string
	 */
	public function cutQuoteBlock($body, $is_html = false);

	/**
	 * Get the full quote block
	 *
	 * @param string $body
	 * @param bool $is_html
	 * @return string
	 */
	public function getQuoteBlock($body, $is_html = false);

	/**
	 * Get the quoted message
	 * 
	 * @param string $body
	 * @param bool $is_html
	 * @return string
	 */
	public function getQuotedMessage($body, $is_html = false);
}