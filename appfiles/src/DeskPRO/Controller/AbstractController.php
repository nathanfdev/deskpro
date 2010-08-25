<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Controller
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\Controller;

/**
 * The abstract controller sets up some default objects.
 */
abstract class AbstractController extends \Symfony\Bundle\FrameworkBundle\Controller
{
	/**
	 * Entity manager
	 * @var Doctrine\ORM\EntityManager
	 */
	protected $em;

	/**
	 * Plain database connection for raw queries
	 * @var DeskPRO\DBAL\Connection
	 */
	protected $db;

	/**
	 * The template engine
	 * @var DeskPRO\Templating\Engine
	 */
	protected $tpl;



	public function __construct(\Symfony\Components\DependencyInjection\Container $container)
	{
		parent::__construct($container);

		$this->em = $this['doctrine.orm.entity_manager'];
		$this->db = $this['database_connection'];

		$this->tpl = $this['templating'];
		$this->tpl->resetTemplateVars();

		$this->init();
	}

	/**
	 * An empty callback function
	 */
	protected function init()
	{

	}
}