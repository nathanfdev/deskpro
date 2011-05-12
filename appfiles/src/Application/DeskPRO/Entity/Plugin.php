<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Entity;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A plugin is a group of event listeners and other resources.
 *
 * @orm:Entity
 * @orm:Table(name="plugins")
 */
class Plugin extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @orm:Id
	 * @orm:Column(name="id", type="string", length=255)
	 */
	protected $id = null;

	/**
	 * @var string
	 * @orm:Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * @var string
	 * @orm:Column(name="description", type="text")
	 */
	protected $description;

	/**
	 * @var string
	 * @orm:Column(name="version", type="string", length=100)
	 */
	protected $version;
	
	/**
	 * The name of the class that describes the plugin and knows how to
	 * install/uninstall etc.
	 *
	 * @var string
	 * @orm:Column(name="package_class", type="string", length=255)
	 */
	protected $package_class = null;

	/**
	 * The file of the package class
	 *
	 * @var string
	 * @orm:Column(name="package_class_file", type="string", length=255)
	 */
	protected $package_class_file = null;

	/**
	 * The path where this plugins Resources directory can be found
	 *
	 * @var string
	 * @orm:Column(name="resources_path", type="string", length=255)
	 */
	protected $resources_path = null;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="PluginListener", mappedBy="plugin", cascade={"persist", "remove", "merge"})
	 */
	protected $listeners;

	/**
	 * An array of namespace=>path that this plugin uses.
	 * This is just a cache version from PluginPackage
	 *
	 * @var string
	 * @orm:Column(name="autoload_paths", type="array")
	 */
	protected $autoload_paths = null;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;


	public function __construct()
	{
		$this->date_created = new \DateTime();
	}

	public function addPluginListener(PluginListener $plugin_listener)
	{
		$this->listeners->add($plugin_listener);
		$plugin_listener->plugin = $this;
	}

	public function getPackageClass()
	{
		require_once($this->package_class_file);
		return $this->package_class;
	}
}