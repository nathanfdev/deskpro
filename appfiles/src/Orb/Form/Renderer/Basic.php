<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Form
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Orb\Form\Renderer;

class Basic implements RendererInterface
{
	/**
	 * An array of fieldtypes we know how to render
	 * 
	 * @var array
	 */
	protected $fieldtype_renderer = array();


	/**
	 * Set a specific renderer for a particular field. You can override the default
	 * behavior for a specific field this way, or add new non-default fields.
	 *
	 * $renderer can be an object (of RendererInterface), or it can be a string classname
	 * that will be instantiated automatically, or a callback function that we'll expect
	 * to return a new instance of the renderer.
	 *
	 * @param  string $field_classname  The classname of the field that will use the renderer
	 * @param  mixed  $renderer         The renderer to use
	 */
	public function addFieldRenderer($field_classname, $renderer)
	{
		$this->fieldtype_renderer[$field_classname] = $renderer;
	}





	/**
	 * Render the field
	 *
	 * @param  Orb\Form\Field\Field $field       The field that needs rendering
	 * @param  array                $attributes  Attributes of the field
	 * @return string HTML
	 */
	public function renderField(\Orb\Form\Field\Field $field, array $attributes = array())
	{
		$field_classname = get_class($field);

		#------------------------------
		# We might have a renderer for this field
		#------------------------------

		$renderer = $this->getFieldRenderer($field_classname);
		if ($renderer) {
			return $renderer->renderField($field, $attributes);
		}


		#------------------------------
		# Otherwise we'll build it up ourselves using the basic renderer
		#------------------------------

		$html = '';

		switch ($field_classname) {
			case 'Orb\\Form\\Field\\Text':
			case 'Orb\\Form\\Field\\Password':
			case 'Orb\\Form\\Field\\Hidden':
			case 'Orb\\Form\\Field\\Checkbox':
			case 'Orb\\Form\\Field\\Radio':
				if (!isset($attributes['type'])) $attributes['type'] = 'input';
				$html = $this->renderTag($field, 'input', $attributes);
				break;

			case 'Orb\\Form\\Field\\Choice':
				$html = $this->_renderChoiceField($field, $attributes);

			case 'Orb\\Form\\Field\\Text':
				$html = $this->renderContentTag($field, 'textarea', $attributes);
				break;
		}
	}


	protected function _renderChoiceField(\Orb\Form\Field\Choice $field, array $attributes)
	{
		$choice_attributes = array();
		if ($attributes['choice_attributes']) {
			$choice_attr = $attributes['choice_attributes'];
			unset($attributes['choice_attributes']);
		}

		if ($attributes['type'] == 'checkbox' AND $attributes['multiple']) {
			$type = 'checkbox';
			$mult = true;
		} elseif ($attributes['type'] == 'radio' AND !$attributes['multiple']) {
			$type = 'radio';
			$mult = false;
		} else {
			$type = 'select';
			$mult = $attributes['multiple'];
		}

		$name = $field->getFormName();
		if ($mult) {
			$name .= '[]';
		}

		$html = array();

		if ($type == 'select') {
			unset($attributes['value']);
			$attributes['name'] = $name;
			$html[] = '<select ' . Strings::htmlAttributes($attributes) . '>';
		}

		$close_section = false;

		$x = 0;
		foreach ($field->getChoices() as $choice) {
			$choice_attributes['id'] = $field->getFormId() . '_' . $x++;

			$is_selected = $field->isValueSelected($choice['value']);

			if ($type == 'select') {
				if ($choice['type'] == 'sectionstart') {
					if ($close_section) {
						$html[] = '</optgroup>';
					}
					$html[] = '<optgroup label="' . htmlspecialchars($choice['label']) . '">';
				} else {
					$choice_attributes['value'] = $choice['value'];
					unset($choice_attributes['selected']);

					if ($is_selected) {
						$choice_attributes['selected'] = true;
					}

					$html[] = '<option ' . Strings::htmlAttributes($choice_attributes) . '>' . htmlspecialchars($choice['label']) . '</option>';
				}
			} else {
				if ($choice['type'] == 'sectionstart') {
					if ($close_section) {
						$html[] = '</div>';
					}
					$html[] = '<div class="section"><div class="section-label">' . htmlspecialchars($choice['label']) . '</div>';
				} else {

					unset($choice_attributes['checked']);

					if ($is_selected) {
						$choice_attributes['checked'] = true;
					}

					$choice_attributes['value'] = $choice['value'];

					$html[] .= '<div class="choice"><label for="' . $choice_attributes['id'] . '">' . htmlspecialchars($choice['label']) . ' ';

					if ($type == 'radio') {
						$html[] = '<input type="radio" ' . Strings::htmlAttributes($choice_attributes) . ' />';
					} else {
						$html[] = '<input type="checkbox" ' . Strings::htmlAttributes($choice_attributes) . ' />';
					}

					$html[] = '</div>';
				}
			}

			if ($choice['type'] == 'sectionstart') {
				$close_section = true;
			}
		}

		if ($close_section) {
			$html[] = ($type == 'select' ? '</optgroup>' : '</div>');
		}

		if ($type == 'select') {
			$html[] = '</select>';
		}

		$html = implode('', $html);

		return $html;
	}

	
	/**
	 * Render a simple tag
	 *
	 * @param  string  $tagname
	 * @param  array   $attributes
	 * @return string
	 */
	public function renderTag(\Orb\Form\Field\Field $field, $tagname, array $attributes)
	{
		$html = '';

		$tagname = strtolower($tagname);

		$html .= "<$tagname " . Strings::htmlAttributes($attributes);
		if ($tagname == 'input') {
			$html .= '/>';
		} else {
			$html .= "</$tagname>";
		}

		if ($field->hasOption('label')) {
			$html .= ' <label for="' . $attributes['id'] . '">' . $field->getOption('label') . '</label> ';
		}

		return $html;
	}


	
	/**
	 * Render a tag that has inner content
	 *
	 * @param  string  $tagname
	 * @param  array   $attributes
	 * @return string
	 */
	public function renderContentTag(\Orb\Form\Field\Field $field, $tagname, array $attributes)
	{
		$tagname = strtolower($tagname);

		$html = "<$tagname " . Strings::htmlAttributes($attributes);
		$html .= htmlspecialchars($field->getFormData());
		$html .= "</$tagname>";

		return $html;
	}




	/**
	 * Get a renderer for a specific field.
	 *
	 * @param string $field_classname
	 * @return RendererInterface
	 */
	public function getFieldRenderer($field_classname)
	{
		if (!isset($this->fieldtype_renderer[$field_classname])) {
			return null;
		}

		$val = $this->fieldtype_renderer[$field_classname];
		if ($val instanceof RendererInterface) {
			return $val;
		}

		$obj = false;
		if (is_callable($val)) {
			$obj = call_user_func($val);
		} elseif (is_string($val) AND class_exists($val)) {
			$obj = new $val();
			if (!($obj instanceof RendererInterface)) {
				throw new \UnexpectedValueException('A mapped field renderer for `'.$field_classname.'` is invalid: It must implement RendererInterface');
			}
		}

		if (!$obj) {
			throw new \UnexpectedValueException('A mapped field renderer for `'.$field_classname.'` is invalid. It is not a renderer, a callback or a classname: ' . $val);
		}

		$this->fieldtype_renderer[$field_classname] = $obj;

		return $obj;
	}
}