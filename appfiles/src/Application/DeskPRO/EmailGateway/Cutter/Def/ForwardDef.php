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

interface ForwardDef
{
	/**
	 * Get an array of info from the forwarded block
	 * 
	 * @param string $body
	 * @param bool $is_html
	 * @return array
	 */
	public function getForwardInfo($body, $is_html = false);

	/**
	 * Cut out the forward block from the body
	 * 
	 * @param string $body
	 * @param bool $is_html
	 * @return string
	 */
	public function cutForwardBlock($body, $is_html = false);

	/**
	 * Get the full forward block
	 *
	 * @param string $body
	 * @param bool $is_html
	 * @return string
	 */
	public function getForwardBlock($body, $is_html = false);

	/**
	 * Get the forwarded message (miunus the header forwarded block);
	 * 
	 * @param string $body
	 * @param bool $is_html
	 * @return string
	 */
	public function getForwardedMessage($body, $is_html = false);
}