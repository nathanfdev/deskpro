<?php
namespace Application\DeskPRO\Widgets;

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Widgets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

/**
 * A widget handler is used when it comes time to render a widget to the interface.
 *
 * This default handler doesn't actually do anything, all of the work is expected to
 * be done by the template and/or JS handler.
 */
class WidgetHandler implements \ArrayAccess
{
	protected $widget;
	protected $options;

	protected $data = array();

	public function __construct(Entity\Widget $widget, array $options = array())
	{
		$this->widget = $widget;
		$this->options = $options;

		if ($this->widget['data']) {
			$this->data = $this->widget['data'];
		}

		$this->data['widget']        = $widget;
		$this->data['id']            = $widget['id'];
		$this->data['name_id']       = $widget['name_id'];
		$this->data['note']          = $widget['note'];
		$this->data['section']       = $widget['section'];
		$this->data['template_name'] = $widget['template_name'];
		$this->data['template_name'] = $widget['template_name'];

		$this->data['options'] = $options;

		$this->init();
	}

	protected function init() { }



	/**
	 * Get the options to pass to the JS initiator
	 *
	 * @return array
	 */
	public function getJsInitObject($element_selector)
	{
		if (!$this->widget['js_widget_class']) {
			return null;
		}

		$info = array();

		// Info used when creating the JS object
		$info['class']   = $this->widget['js_widget_class'];
		$info['prefs']   = $this->getPrefsForCurrentPerson();
		$info['wrapperSelector'] = $element_selector;

		// Options that are passed to the JS object
		$info['options'] = array();
		$info['options']['id']      = $this->widget['id'];
		$info['options']['name_id'] = $this->widget['name_id'];

		return $info;
	}



	/**
	 * Get preferences for this widget based on the current person.
	 * This will fetch from the person prefs, and then from session.
	 *
	 * @return array
	 */
	public function getPrefsForCurrentPerson()
	{
		$prefs = array();

		$pref_prefix = 'widget.' . $this->widget['name_id'] . '.';

		// From person
		$person = App::getCurrentPerson();
		if ($person AND $person['id']) {
			$prefs = $person->loadPrefGroup($pref_prefix);
		}

		// From session
		$session = App::getSession();
		if ($session) {
			foreach ($session->getAttributes() as $k => $v) {
				if (strpos($k, $pref_prefix) === 0) {
					$k = str_replace($k, '', $k);
					$prefs[$k] = $v;
				}
			}
		}

		return $prefs;
	}

	public function offsetUnset($offset)
	{

	}

	public function offsetSet($offset, $value)
	{

	}

	public function offsetGet($offset)
	{
		return $this->data[$offset];
	}

	public function offsetExists($offset)
	{
		return isset($this->data[$offset]);
	}
}
