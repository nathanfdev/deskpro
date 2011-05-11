<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Plugin
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Plugin;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Plugin;

use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * This attaches itself as a listener to all events used by plugins. When an event is fired,
 * plugins listener classes are lazy-initialized and run.
 */
class EventPluginDelegator
{
	/**
	 * @var \Symfony\Component\EventDispatcher\EventDispatcher
	 */
	protected $event_dispatcher;

	/**
	 * @var string[]
	 */
	protected $listened_events = array();

	/**
	 * @var \Application\DeskPRO\Plugin\PluginListenerFactory
	 */
	protected $plugin_listener_factory;

	/**
	 * @var \Application\DeskPRO\Entity\PluginListener[]
	 */
	protected $plugin_listeners = array();

	/**
	 * These are instantiaed plugin listener classes
	 * @var array
	 */
	protected $plugin_listener_objs = array();

	public static function newWithInstalledPlugins()
	{
		$ead = new self();

		
	}

	public function __construct(EventDispatcher $event_dispatcher = null)
	{
		if ($event_dispatcher === null) {
			$event_dispatcher = App::getEventDispatcher();
		}
		
		$this->event_dispatcher = $event_dispatcher;

		$this->plugin_listener_factory = new PluginListenerFactory();
	}


	/**
	 * Add plugins to the listener
	 * 
	 * @param  $plugins
	 * @return void
	 */
	public function addPluginListeners(array $plugin_listeners)
	{
		$events = array();
		foreach ($plugin_listeners as $plugin_listener) {
			$event_name = $plugin_listener['event_name'];

			if (!isset($this->plugin_listeners[$event_name])) $this->plugin_listeners[$event_name] = array();
			$this->plugin_listeners[$event_name][] = $plugin_listener;

			$events[] = $event_name;
		}

		$this->listenForEvents($events);
	}

	/**
	 * Listen on these event names
	 *
	 * @param array $events
	 * @return void
	 */
	public function listenForEvents(array $events)
	{
		$this->listened_events = array_merge($this->listened_events, $events);
		$this->listened_events = array_unique($this->listened_events);

		foreach ($events as $event) {
			$this->event_dispatcher->addListener($events, $this);
		}
	}
	

	/**
	 * Gets all the event handler classes
	 * 
	 * @param  $event_name
	 * @return array
	 */
	public function getEventRunners($event_name)
	{
		if (empty($this->plugin_listeners[$event_name])) return array();

		$runners = array();

		foreach ($this->plugin_listeners[$event_name] as $plugin_listener) {
			$id = $plugin_listener['id'];
			if (isset($this->plugin_listener_objs[$id])) {
				$runner = $this->plugin_listener_objs[$id];
			} else {
				$runner = $this->plugin_listener_factory->create($plugin_listener);
				$this->plugin_listener_objs[$id] = $runner;
			}

			$runners[] = $runner;
		}
		
		return $runners;
	}

	
	/**
	 * Handles all event calls and delegates them to the plugins
	 *
	 * @throws \BadMethodCallException
	 * @param  $method
	 * @param  $args
	 * @return void
	 */
	public function __call($method, $args)
	{
		if (!in_array($method, $this->listened_events)) {
			throw new \BadMethodCallException("`$method` is an invlaid event name for this listener");
		}

		$runners = $this->getEventRunners($method);

		foreach ($runners as $runner) {
			call_user_func_array(array($runner, $method), $args);
		}
	}
}