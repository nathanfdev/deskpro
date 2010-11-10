<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Form
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Orb\Form\Field;

use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * A form field.
 *
 * Inspired by sf2's Field.
 *
 * @option  string    name        The name of this field as it'll be in the forms etc. This must be unique per group.
 * @option  Field     parent      A parent field, if any (also see setParentField)
 * @option  Renderer  renderer    The renderer to use, if you want to render the field (also see setRenderer)
 * @option  array     attribtues  Default attributes used in when calling render(), it'll be merged with attributes supplied
 * @option  bool      no_render   Signifies to the renderer that the field should not be rendered (it'll return an empty string)
 */
abstract class Field
{
	/**
	 * The parent field, if there is one
	 * @var Field
	 */
	protected $parent;

	/**
	 * The filter chain
	 * @var Orb\Filter\FilterChain
	 */
	protected $filter;

	/**
	 * The validator chain
	 * @var Orb\Validator\ValidatorChain
	 */
	protected $validator;

	/**
	 * The transformer chain
	 * @var Orb\Form\Transformer\TransformerChain
	 */
	protected $transformer;

	/**
	 * The renderer to use
	 * @var Orb\Form\Renderer\RendererInterface
	 */
	protected $renderer = null;

	/**
	 * An array of options.
	 * @var array
	 */
	protected $options = array();

	/**
	 * Data we can store etc
	 * @var mixed
	 */
	protected $data;

	/**
	 * Raw form data
	 * @var mixed
	 */
	protected $form_data;

	/**
	 * Has the field been modified by a posted form?
	 * @var string
	 */
	protected $is_modified = false;



	/**
	 * @param array $options
	 */
	public function __construct(array $options = array())
	{
		$this->options = $options;

		$this->filter = new \Orb\Filter\FilterChain();
		$this->validator = new \Orb\Validator\ValidatorChain();
		$this->transformer = new \Orb\Form\Transformer\TransformerChain();

		if ($this->hasOption('parent')) {
			$this->setParentField($this->getOption('parent'));
		}

		if ($this->hasOption('renderer')) {
			$this->setRenderer($this->getOption('renderer'));
		}

		if ($this->hasOption('data')) {
			$this->setData($this->getOption('data'));
		}

		$this->init();
	}


	
	/**
	 * Empty callback fired at the end of construction
	 */
	protected function init()
	{

	}

	

	/**
	 * Set the parent field
	 * 
	 * @param Field $parent
	 */
	public function setParentField(Field $parent)
	{
		$this->parent = $parent;
	}
	

	
	/**
	 * Get the name of this field. The name is unique per group.
	 * 
	 * @return string
	 */
	public function getName()
	{
		if ($this->getOption('name') === null) {
			throw new \UnexpectedValueException('The `name` option is not set');
		}

		return $this->getOption('name');
	}



	/**
	 * Get the name of this form element.
	 *
	 * @return string
	 */
	public function getFormName()
	{
		if ($this->getOption('form_name') !== null) {
			return $this->getOption('form_name');
		}

		if ($this->getOption('name') === null) {

			// If the parent is a composite field, we can guess the name
			// is just a numeric index.
			if ($this->parent AND $this->parent instanceof FieldGroup AND $this->parent->isComposite()) {
				$this->setOption('name', count($this->parent));
			} else {
				throw new \UnexpectedValueException('The `name` option is not set');
			}
		}

		if ($this->parent) {
			$name = $this->parent->getFormName() . '[' . $this->getOption('name') . ']';
		} else {
			$name = $this->getOption('name');
		}

		$this->setOption('form_name', $name);

		return $name;
	}


	
	/**
	 * Get the ID of this form element.
	 *
	 * @return string
	 */
	public function getFormId()
	{
		if ($this->getOption('form_id') !== null) {
			return $this->getOption('form_id');
		}

		$id = preg_replace('#\[(.*?)\]#', '_$1', $this->getFormName());

		$this->setOption('form_id', $id);

		return $id;
	}


	
	/**
	 * Instantiate and add a new named filter. The name should be a dashed
	 * name of the classname in either the Orb\Filter or Zend\Filter namespace.
	 *
	 * For example string-trim will map to Zend\Filter\StringTrim.
	 *
	 * @param string $name The filter name
	 */
	public function addNamedFilter($name)
	{
		$classname = Strings::dashToCamelCase($name);
		$classname = ucfirst($classname);

		$ns_classname = 'Orb\\Filter\\' . $classname;
		if (!class_exists($ns_classname)) {
			$ns_classname = 'Zend\\Filter\\' . $classname;
		}
		if (!class_exists($ns_classname)) {
			throw new \InvalidArgumentException('Could not find filter name `'.$name.'`');
		}

		$filter = new $ns_classname();
		$this->addFilter($filter);
	}



	/**
	 * Add a new filter to this field
	 *
	 * @param \Zend\Filter\Filter $filter
	 */
	public function addFilter(\Zend\Filter\Filter $filter)
	{
		$this->filter->addFilter($filter);
	}

	

	/**
	 * Get an array of set filters
	 *
	 * @return array
	 */
	public function getFilters()
	{
		return $this->filters;
	}



	/**
	 * Add a transformer to this field.
	 *
	 * @param Orb\Form\Transformer $transformer
	 */
	public function addTransformer(\Orb\Form\Transformer\TransformerInterface $transformer)
	{
		$this->transformer->addTransformer($transformer);
	}

	

	/**
	 * Get an array of set transformers
	 *
	 * @return array
	 */
	public function getTransformers()
	{
		return $this->transformer->getTransformers();
	}


	
	/**
	 * Add a validator to the chain.
	 *
	 * @param  \Orb\Validator\AbstractValidator  $validator         The validator to add
	 * @param  bool                              $break_on_invalid  If the validator says the value is invalid, break the chain (stop executing further ones)
	 */
	public function addValidator(\Orb\Validator\AbstractValidator $validator, $break_on_invalid = false)
	{
		$this->validator->addValidator($validator, $break_on_invalid);
	}

	

	/**
	 * Get an array of set validators
	 *
	 * @return array
	 */
	public function getValidators()
	{
		return $this->validator->getValidators();
	}


	
	/**
	 * Check if the value is valid.
	 *
	 * @return bool
	 */
	public function isValid()
	{
		return $this->validator->isValid($this->getData());
	}


	
	/**
	 * Get an array of error codes
	 * @return array
	 */
	public function getErrors()
	{
		return $this->validator->getErrors();
	}



	/**
	 * Set the field data (ie value stored in a database)
	 * 
	 * @param mixed $data The data to set
	 */
	public function setData($data)
	{
		$this->data = $data;
		$this->form_data = $this->transformer->transformStoredToForm($data);
	}



	/**
	 * Get the field data
	 *
	 * @return mixed
	 */
	public function getData()
	{
		return $this->data;
	}



	/**
	 * Set form data (ie from POST).
	 *
	 * @param mixed $form_data The user input value to set
	 */
	public function setFormData($form_data)
	{
		$this->data = $this->transformer->transformFormToStored($form_data);
		$this->form_data = $this->transformer->transformStoredToForm($this->data);

		/*
		$new_form_data = $this->transformer->transformStoredToForm($this->data);
		if ($this->_compareFormData($this->form_data, $new_form_data)) {
			$this->is_modified = true;
			$this->form_data = $this->transformer->transformStoredToForm($this->data);
		}
		*/
	}

	protected function _compareFormData($old, $new)
	{
		if (is_array($old) AND is_array($new)) {
			foreach ($old as $k => $v) {
				if (!isset($new[$k])) {
					return false;
				}
				if (!$this->_compareFormData($k, $new[$k])) {
					return false;
				}
			}
			return true;
		} elseif ($old == $new) {
			return true;
		} else {
			return false;
		}
	}

	

	/**
	 * Has this fields value changed from POST?
	 *
	 * @return bool
	 */
	public function isModified()
	{
		return $this->is_modified;
	}


	
	/**
	 * Get the form data.
	 * 
	 * @return mixed
	 */
	public function getFormData()
	{
		return $this->form_data;
	}


	
	/**
	 * Render the field into HTML
	 * 
	 * @param array $attributes
	 */
	public function render(array $attributes = array())
	{
		$renderer = $this->getRenderer();

		if ($renderer === null) {
			throw new \RuntimeException('No renderer has been set, cannot render field');
		}

		$attributes = array_merge($this->getDefaultAttributes(), $attributes);

		return $renderer->renderField($this, $attributes);
	}


	
	/**
	 * Set the renderer
	 *
	 * @param \Orb\Form\Renderer\RendererInterface $renderer
	 */
	public function setRenderer(\Orb\Form\Renderer\RendererInterface $renderer)
	{
		$this->renderer = $renderer;
	}



	/**
	 * Get the renderer. This'll be the renderer set, or it'll try and use a parents
	 * renderer.
	 *
	 * Returns null if there is no renderer.
	 * 
	 * @return Orb\Form\Renderer\RendererInterface
	 */
	public function getRenderer()
	{
		if ($this->renderer) {
			return $this->renderer;
		} elseif ($this->parent AND $this->parent->getRenderer()) {
			return $this->parent->getRenderer();
		}

		return null;
	}


	
	/**
	 * An array of default attributes for this field.
	 * 
	 * @return array
	 */
	public function getDefaultAttributes()
	{
		if ($this->hasOption('attributes')) {
			$attr = $this->getOption('attributes');
		} else {
			$attr = array();
		}

		$attr['name']  = $this->getFormName();
		$attr['id']    = $this->getFormId();
		$attr['value'] = $this->getFormData();
		if ($attr['value'] === null) {
			$attr['value'] = '';
		}

		return $attr;
	}

	

	/**
	 * Get the value if an option $name, or return $default if it doesn't exist.
	 *
	 * @see setOption
	 * @param  string  $name     The option to fetch
	 * @param  mixed   $default  The default value to return if $name doesn't exist
	 * @return mixed
	 */
	public function getOption($name, $default = null)
	{
		return isset($this->options[$name]) ? $this->options[$name] : $default;
	}


	
	/**
	 * Set an option.
	 *
	 * Standard options used by most renderers:
	 * - label
	 * - description
	 *
	 * @param  stting  $name
	 * @param  mixed   $value
	 */
	public function setOption($name, $value)
	{
		$this->options[$name] = $value;
	}



	/**
	 * Set an array of options.
	 *
	 * If $do_merge is false, then the entire options array is overwritten with your
	 * passed value. Otherwise, they are merged together.
	 *
	 * @param  array  $options   Array of k=>v options to set
	 * @param  bool   $do_merge  Merge passed options with existing set options?
	 */
	public function setOptions(array $options, $do_merge = true)
	{
		if ($do_merge AND $this->options) {
			$this->options = array_merge($this->options, $options);
		} else {
			$this->options = $options;
		}
	}


	
	/**
	 * Get the entire array of options
	 *
	 * @return mixed
	 */
	public function getOptions()
	{
		return $this->options;
	}

	

	/**
	 * Check to see if an option has been set
	 *
	 * @param string $name
	 * @return bool
	 */
	public function hasOption($name)
	{
		return isset($this->options[$name]);
	}


	
	/**
	 * A simple string representation of the fields current value.
	 * This should be a usable value that makes sense. For example, rendering
	 * a date to YYYY-MM-DD string.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return 'Field(' . get_class($this) . ')';
	}
}