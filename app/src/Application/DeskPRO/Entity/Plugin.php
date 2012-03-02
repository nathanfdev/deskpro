<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A plugin is a group of event listeners and other resources.
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="plugins")
 */
class Plugin extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\Column(name="id", type="string", length=255)
	 */
	protected $id = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="description", type="text")
	 */
	protected $description;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="version", type="string", length=100)
	 */
	protected $version;
	
	/**
	 * The name of the class that describes the plugin and knows how to
	 * install/uninstall etc.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="package_class", type="string", length=255)
	 */
	protected $package_class = null;

	/**
	 * The file of the package class
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="package_class_file", type="string", length=255)
	 */
	protected $package_class_file = null;

	/**
	 * The path where this plugins Resources directory can be found
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="resources_path", type="string", length=255)
	 */
	protected $resources_path = null;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="PluginListener", mappedBy="plugin", cascade={"persist", "remove", "merge"})
	 */
	protected $listeners;

	/**
	 * An array of namespace=>path that this plugin uses.
	 * This is just a cache version from PluginPackage
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="autoload_paths", type="array")
	 */
	protected $autoload_paths = null;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;


	public function __construct()
	{
		$this->listeners = new \Doctrine\Common\Collections\ArrayCollection();
		$this->date_created = new \DateTime();
	}

	public function addPluginListener(PluginListener $plugin_listener)
	{
		$this->listeners->add($plugin_listener);
		$plugin_listener->plugin = $this;
	}

	public function getPackageClass()
	{
		return $this->package_class;
	}
}
