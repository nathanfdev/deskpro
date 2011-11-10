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
	protected $variables = array();

	public function setVariable($name, $value)
	{
		$this->variables[$name] = $value;
	}

	public function getVariable($name)
	{
		return isset($this->variables[$name]) ? $this->variables[$name] : null;
	}

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

	public function isDebug()
	{
		return App::isDebug();
	}

	public function getStyle()
	{
		return App::getSystemService('style');
	}

	public function __get($name)
	{
		if (isset($this->variables[$name])) {
			return $this->variables[$name];
		}

		return null;
	}

	public function __isset($name)
	{
		return isset($this->variables[$name]);
	}

	public function getLastException()
	{
		if (!App::has('deskpro.exception_logger')) {
			return null;
		}

		$logger = App::get('deskpro.exception_logger');
		return $logger->getLastException();
	}
}
