<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EmailGateway\Cutter;

class ForwardCutter
{
	protected $body;

	/**
	 * Check if a subject matches the pattern for a forwarded message.
	 * 
	 * @param string $subject
	 * @return bool
	 */
	public static function subjectIsForward($subject)
	{
		// Prefixes for FW/FWD and in other langs too
		return (bool)preg_match('#^(FW|FWD|VL|WG|FS|VB|RV|VS):#i', ltrim($subject));
	}

	public function __construct($body)
	{
		$this->body = $body;
	}
}