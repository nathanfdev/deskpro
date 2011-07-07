<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Templating
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Templating;

use Application\DeskPRO\App;

use Symfony\Bundle\FrameworkBundle\Templating\GlobalVariables as BaseGlobalVariables;

class GlobalVariables extends BaseGlobalVariables
{
	public function getUser()
	{
		return App::getCurrentPerson();
	}

	public function getSetting($name)
	{
		return App::getSetting($name);
	}

	public function getSession()
	{
		return App::getSession();
	}

	public function getVisitor()
	{
		return App::getSession()->getVisitor();
	}
}