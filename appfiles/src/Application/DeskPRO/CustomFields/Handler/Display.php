<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage CustomFields
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\CustomFields\Handler;

use Symfony\Component\EventDispatcher\EventDispatcher;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

/**
 * A display field doesn't actually have any form or anything (unless of course a plugin
 * says it does).
 */
class Display extends HandlerAbstract
{
	const EVENT_RENDER_HTML = 'DeskPRO_onDisplayFieldRenderHtml';
	const EVENT_RENDER_TEXT = 'DeskPRO_onDisplayFieldRenderText';
	const EVENT_FORM_FIELD  = 'DeskPRO_onDisplayFieldFormField';
	const EVENT_READ_FORM   = 'DeskPRO_onDisplayFieldReadForm';

	/**
	 * @var \Symfony\Component\EventDispatcher\EventDispatcher
	 */
	protected $event_dispatcher;

	public function __construct(Entity\CustomDefAbstract $field_def = null, EventDispatcher $event_dispatcher = null)
	{
		parent::__construct($field_def);

		if ($event_dispatcher === null) {
			$this->event_dispatcher = App::getEventDispatcher();
		}

		$this->event_dispatcher = $event_dispatcher;
	}

	public function makeEventObject(array $data = array())
	{
		$ev = new DisplayEvent($this->field_def, $data);
		return $ev;
	}

	/**
	 * Render the field to HTML for use in a web page.
	 */
	public function renderHtml(array $data = null)
	{
		if ($data === null) return '';

		$ev = $this->makeEventObject(array('data' => $data, 'html' => $this->field_def->getOption('html', '')));
		$this->event_dispatcher->dispatch(self::EVENT_RENDER_HTML, $ev);

		if ($ev->html) {
			return $ev->html;
		}

		return htmlspecialchars($this->renderText($data));
	}


	/**
	 * Render the field
	 */
	public function renderText(array $data = null)
	{
		if ($data === null) return '';

		$ev = $this->makeEventObject(array('data' => $data, 'text' => strip_tags($this->field_def->getOption('html', ''))));
		$this->event_dispatcher->dispatch(self::EVENT_RENDER_TEXT, $ev);

		if ($ev->text) {
			return $ev->text;
		}

		if (!empty($data['value'])) {
			$txt = $data['value'];
		} else {
			$txt = '';
		}

		return $txt;
	}


	/**
	 * Render a field in a given context. This is just a strategy for calling other renderX
	 * methods.
	 *
	 * $data is a data structure `array(value=>..., children=>array(...))` as returned
	 * from `Application\DeskPRO\CustomFields\Util::createDataHierarchy()`
	 *
	 * @param string $context
	 * @param array $data
	 * @return mixed
	 */
	public function renderContext($context, array $data)
	{
		switch ($context) {
			case self::CONTEXT_HTML:
				$method = 'renderHtml';
				break;

			case self::CONTEXT_TEXT:
				$method = 'renderText';
				break;

			default:
				throw new \InvalidArgumentException("Unknow context `$context`");
		}

		return $this->$method($data);
	}


	/**
	 * Get the form field
	 *
	 * @return Symfony\Component\Form\Field
	 */
	public function getFormField(array $data = null)
	{
		$ev = $this->makeEventObject(array('data' => $data, 'field' => null));
		$this->event_dispatcher->dispatch(self::EVENT_FORM_FIELD, $ev);

		return $ev->field;
	}


	/**
	 * Get data from a posted form that we'll store in the database.
	 *
	 * This must return an array of array(field_id, type, value)
	 * If no value is set, then use null.
	 *
	 * @return array
	 */
	public function getDataFromForm(array $form_data)
	{
		$ev = $this->makeEventObject(array('form_data' => $form_data, 'return_data' => array($this->field_def['id'], 'input', null)));
		$this->event_dispatcher->dispatch(self::EVENT_READ_FORM, $ev);

		return $ev->return_data;
	}


	/**
	 * Gets an array of search operation types we can perform against this
	 * field.
	 *
	 * @return array
	 */
	public function getSearchCapabilities()
	{
		// Not searchable by default
		return array();
	}


	/**
	 * Get the type of search this field sholud be on.
	 *
	 * - Text/input have 'input'
	 * - Dates/numric have 'value'
	 * - Fields that use an option go by 'id'
	 *
	 * @return string
	 */
	public function getSearchType()
	{
		return 'display';
	}
}
