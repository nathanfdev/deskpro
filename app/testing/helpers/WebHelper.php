<?php
namespace Codeception\Module;

use Application\DeskPRO\Entity\Session;
use Orb\Util\Util;

class WebHelper extends \Codeception\Module
{
	/**
	 * @return \Codeception\Module\DpControlHelper
	 */
	public function getDpControlHelper()
	{
		return $this->getModule('DpControlHelper');
	}
}
