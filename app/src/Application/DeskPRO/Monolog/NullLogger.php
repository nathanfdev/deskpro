<?php

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Application\DeskPRO\Monolog;

use Psr\Log\LoggerInterface;

class NullLogger implements LoggerInterface
{
	public function emergency($message, array $context = array())
	{

	}

	public function alert($message, array $context = array())
	{

	}

	public function critical($message, array $context = array())
	{

	}

	public function error($message, array $context = array())
	{

	}

	public function warning($message, array $context = array())
	{

	}

	public function notice($message, array $context = array())
	{

	}

	public function info($message, array $context = array())
	{

	}

	public function debug($message, array $context = array())
	{

	}

	public function log($level, $message, array $context = array())
	{

	}
}
