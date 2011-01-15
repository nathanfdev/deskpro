<?php

namespace Application\DeskPRO\Build;

use Symfony\Component\Finder\Finder;

use Orb\Util\Arrays;

class Output
{
	public $html = false;

	public function write($line)
	{
		if ($this->html) {
			$line = htmlspecialchars($line);
		}

		echo $line;
	}

	public function writeln($line)
	{
		return $this->write($line . "\n");
	}
}