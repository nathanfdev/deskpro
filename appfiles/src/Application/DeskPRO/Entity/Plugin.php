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
 * Plugins are callbacks that are fired at specific events or points in the code.
 *
 * Some plugins might be tied to specific objects, in which case their 'event_name'
 * will be null. For example, to run a callback on a ticket trigger we don't
 * have a specific event name for that.
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
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY")
	 * @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * @var string
	 * @orm:Column(name="event_name", type="string", length=255, nullable=true)
	 */
	protected $event_name = null;

	/**
	 * The name of the object this plugin is associated with (if its not associated with a general event).
	 * Convention should be "EntityName:ID". For example "TicketTrigger:12"
	 *
	 * @var string
	 * @orm:Column(name="associated_object", type="string", length=255, nullable=true)
	 */
	protected $associated_object = null;

	/**
	 * The name/description of the plugin
	 *
	 * @var string
	 * @orm:Column(name="name", type="string", length=255)
	 */
	protected $name = '';

	/**
	 * @var string
	 * @orm:Column(name="plugin_callback", type="string", length=255)
	 */
	protected $plugin_callback;

	/**
	 * Options we'll pass to the callback
	 *
	 * @var array
	 * @orm:Column(name="callback_options", type="array")
	 */
	protected $callback_options = array();

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;


	public function __construct()
	{
		$this->date_created = new \DateTime();
	}


	/**
	 * Calls the plugin callback and returns its value.
	 *
	 * @return mixed
	 */
	public function executePlugin(array $params = array())
	{
		$options = $this->callback_options;
		$options['plugin'] = $this;

		$options = array_merge($options, $params);

		$ret = call_user_func($this->plugin_callback, $options);

		return $ret;
	}
}