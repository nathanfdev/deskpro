<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Form
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
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

	protected function init()
	{
		if ($this->getOption('event_dispatcher')) {
			$this->event_dispatcer = $this->getOption('event_dispatcher');

			$event = new Event($this, 'orb.form.init');
			$this->event_dispatcer->notify($event);
		}
	}


	/**
	 * Set an array of form values for fields in this group
	 *
	 * @param array $data
	 */
	public function setFormData($form_data)
	{
		if (!is_array($form_data)) {
			throw new \InvalidArgumentException('$form_data must be an array');
		}

		if (!isset($form_data[$this->getName()])) {
			return;
		}

		$form_data = $form_data[$this->getName()];

		parent::setFormData($form_data);
	}


	/**
	 * Render the form tag.
	 * 
	 * @param array $attributes
	 */
	public function renderFormTag(array $attributes = array())
	{
		$attributes = array_merge($this->getDefaultAttributes(), $attributes);

		$html = '<form ' . Strings::htmlAttributes($attributes) . '>';

		if ($this->event_dispatcer) {
			$event = new Event($this, 'orb.form.render_form_tag');
			$html = $this->event_dispatcer->filter($event, $html);
		}

		return $html;
	}

	

	/**
	 * Go through the entire form and render all hidden tags
	 *
	 * @param bool $mark_as_norender Mark the field with no_render option? Prevents them from being rendered again
	 * @return string
	 */
	public function renderHiddenTags($mark_as_norender = false)
	{
		$html = array();

		foreach ($this->findFieldsOfType('Orb\\Form\\Field\\Hidden', true) as $f) {
			$html[] = $f->render();
			if ($mark_as_norender) {
				$f->setOption('no_render', true);
			}
		}

		return implode('', $html);
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