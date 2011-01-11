<?php

namespace Application\DeskPRO\Build;

use Symfony\Component\Finder\Finder;

use Orb\Util\Arrays;

class Output
{
	public $html = false;

	public function write($line, $newline = true)
	{
		if ($this->html) {
			$line = htmlspecialchars($line);
		}

		echo $line;
		if ($newline) {
			echo "\n";// should be pre'd, so dont need htmlspecial
		}
	}
}