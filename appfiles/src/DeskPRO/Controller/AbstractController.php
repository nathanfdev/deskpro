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
use \Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The abstract controller sets up some default objects.
 */
abstract class AbstractController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
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
	 * Shared template vars
	 * @var ArrayObject
	 */
	protected $tplvars;



	public function setContainer(ContainerInterface $container)
	{
		parent::setContainer($container);

		// Set shortcuts once we have the container :)

		$this->em = $this['doctrine.orm.entity_manager'];
		$this->db = $this['database_connection'];

		$this->tplvars = $this['templating']->getTemplateVarsObject();
		$this['templating']->resetTemplateVars();

		$this->init();
	}

	/**
	 * An empty callback function
	 */
	protected function init()
	{

	}
}