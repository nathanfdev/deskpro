<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Util
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Transform markdown-formatted text into html
 */
class Markdown extends \Markdown_Parser
{
	/**
	 * Format the supplied markdown string to HTML
	 * 
	 * @static
	 * @param  $string
	 * @return string
	 */
	public static function format($string)
	{
		$tr = new self();
		return $tr->transform($string);
	}
}