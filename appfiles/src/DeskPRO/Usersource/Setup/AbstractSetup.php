<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Usersources
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\Usersource\Setup;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * The setup classes handle showing the user a wizard, and then taking input and
 * transforming it (if necessary) into adapter options.
 */
abstract class AbstractSetup
{
	protected $form_data = array();
	protected $controller;
	protected $usersource;
	
	/**
	 * Entity manager
	 * @var DeskPRO\ORM\EntityManager
	 */
	protected $em;


	public function __construct(\Application\TechBundle\Controller\AbstractController $controller)
	{
		$this->controller = $controller;
		$this->em = $this->controller['doctrine.orm.entity_manager'];
	}

	public function setExistingUsersource(\Application\CoreBundle\Entity\Usersource $usersource)
	{
		$this->usersource = $usersource;
		$this->form_data = $usersource['adapter_options'];
	}



	/**
	 * Set form data. Returns true if data is okay, false if not.
	 * 
	 * @param array $form_data
	 * @return bool
	 */
	public function setFormData(array $form_data)
	{
		$this->form_data = $form_data;
		return true;
	}


	
	abstract public function getAdapterClass();
	
	abstract public function getAdapterOptions();

	/**
	 * Render the form parts for this setup wizard
	 * @return string
	 */
	public function renderForm()
	{
		$nameparts = explode('\\', \get_class($this));
		$classname = array_pop($nameparts);

		$tpl = 'TechBundle:Usersources:setupform-' . strtolower($classname);

		return $this->controller['templating']->render($tpl, array('form_data' => $this->form_data));
	}


	/**
	 * Set up or update a remote resource. This must setup the primary remoteresource with the
	 * AuthIdentity scraper, as well as any additional resources the Usersource might need
	 * and possibly custom field mappers.
	 * 
	 * @param \Application\CoreBundle\Entity\Usersource $usersource
	 * @return array
	 */
	public function setupRemoteResources(\Application\CoreBundle\Entity\Usersource $usersource)
	{
		if ($usersource['remote_resource']) {
			return;
		}

		$remote_resource = $this->em->createEntity('CoreBundle:RemoteResource');
		$remote_resource['scraper_class'] = 'DeskPRO\\Scraper\\AuthIdentity';
		$this->em->persist($remote_resource);

		$usersource['remote_resource'] = $remote_resource;
	}
}