<?php
/**
 * DeskPRO
 *
 * @package Orb
 * @subpackage Console
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Console\Formatter;

use Symfony\Component\Console\Formatter\OutputFormatter;

class MaxLineLengthFormatter extends OutputFormatter
{
	public $max_length = 80;

	public function __construct($max_length)
	{
		$this->max_length = $max_length;
	}

	public function format($message)
	{
		$message = parent::format($message);
		$message = wordwrap($message, $this->max_length, "\n");

		return $message;
	}
}
