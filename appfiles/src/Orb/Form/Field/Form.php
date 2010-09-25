<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Form
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Orb\Form\Field;

use \Symfony\Component\EventDispatcher\EventDispatcher;
use \Symfony\Component\EventDispatcher\Event;

use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * A field that has any number of sub-fields
 *
 * This can be used to group things like radio fields, or just serve as a namespace
 * because fields in forms are named after their parents.
 *
 * @option bool is_upload True to enable multipart form for uploads
 */
class Form extends FieldGroup
{
	/**
	 * @var Symfony\Component\EventDispatcher\EventDispatcher
	 */
	protected $event_dispatcer;

	public function init()
	{
		if ($this->getOption('event_dispatcher')) {
			$this->event_dispatcer = $this->getOption('event_dispatcher');

			$event = new Event($this, 'orb.form.init');
			$this->event_dispatcer->notify($event);
		}
	}


	/**
	 * Render the form tag.
	 * 
	 * @param array $attributes
	 */
	public function renderFormTag($url, array $attributes = array())
	{
		$attributes = array_merge($this->getDefaultAttributes(), $attributes);
		$attributes['method'] = $url;

		$html = '<form ' . Strings::htmlAttributes($attributes) . '>';

		if ($this->event_dispatcer) {
			$event = new Event($this, 'orb.form.render_form_tag');
			$html = $this->event_dispatcer->filter($event, $html);
		}

		return $html;
	}


	
	public function getDefaultAttributes()
	{
		$attr = parent::getDefaultAttributes();

		if ($this->hasOption('is_upload')) {
			$attr['enctype'] = 'multipart/form-data';
		}

		$attr['method'] = 'POST';

		unset($attr['value']);

		return $attr;
	}
}