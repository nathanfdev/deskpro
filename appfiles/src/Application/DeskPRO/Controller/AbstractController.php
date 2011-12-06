<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Controller
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Application\DeskPRO\App;

use Application\DeskPRO\Build\Upgrader;
use Application\DeskPRO\Build\VersionReader;

/**
 * The abstract controller sets up some default objects.
 *
 * @property $em \Doctrine\ORM\EntityManager
 * @property $db \Application\DeskPRO\DBAL\Connection
 * @property $in \Orb\Input\Reader\Reader
 * @property $cleaner \Orb\Input\Cleaner\Cleaner
 * @property $tpl \Application\DeskPRO\Templating\Engine
 * @property $settings \Application\DeskPRO\Settings\Settings
 * @property $session \Application\DeskPRO\HttpFoundation\Session
 */
abstract class AbstractController extends \Application\DeskPRO\HttpKernel\Controller\Controller
{
	public function __get($prop)
	{
		switch ($prop) {
			case 'em': return $this->get('doctrine.orm.entity_manager');
			case 'db': return $this->get('database_connection');
			case 'in': return $this->get('deskpro.core.input_reader');
			case 'cleaner': return $this->get('deskpro.core.input_cleaner');
			case 'settings': return $this->get('deskpro.core.settings');
			case 'session': return $this->get('session');
			case 'tpl': return $this->get('templating');
			default:
				throw new \InvalidArgumentException("Unknown property {$prop}");
		}
	}

	/**
	 * Is this a POST request?
	 *
	 * @return bool
	 */
	public function isPostRequest()
	{
		return ($this->get('request')->getMethod() == 'POST');
	}
}
