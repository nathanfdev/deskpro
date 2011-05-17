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
 * These are callbacks that are fired at specific events or points in the code.
 *
 * @orm:Entity
 * @orm:Table(name="plugin_listeners")
 */
class PluginListener extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY")
	 * @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Plugin
	 * @orm:ManyToOne(targetEntity="Plugin", inversedBy="plugins")
	 * @orm:JoinColumn(name="plugin_id", referencedColumnName="id")
	 */
	protected $plugin;

	/**
	 * @var string
	 * @orm:Column(name="event_name", type="string", length=255, nullable=true)
	 */
	protected $event_name = null;

	/**
	 * Options for event listener connector. Sometimes an event listener can decide which
	 * plugins to instantiate. For example, if a plugin needs to plug into a specific
	 * field.
	 *
	 * @orm:Column(name="event_options", type="array")
	 */
	protected $event_options = array();

	/**
	 * The description of what this plugin does
	 *
	 * @var string
	 * @orm:Column(name="description", type="string", length=255)
	 */
	protected $description = '';

	/**
	 * @var int
	 * @orm:Column(name="run_order", type="integer")
	 */
	protected $run_order = 0;

	/**
	 * @var string
	 * @orm:Column(name="listener_class", type="string", length=255)
	 */
	protected $listener_class;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;


	public function __construct()
	{
		$this->date_created = new \DateTime();
	}
}