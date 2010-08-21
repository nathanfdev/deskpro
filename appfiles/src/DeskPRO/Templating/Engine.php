<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\Templating;



/**
 * Customized template engine that we can assign shared variables to, and also makes the default
 * renderer twig.
 */
class Engine extends \Symfony\Bundle\FrameworkBundle\Templating\Engine implements \ArrayAccess
{
	/**
	 * An array of shared template vars
	 * @var array
	 */
	protected $_tpl_vars = array();



	public function render($name, array $parameters = array())
	{
		$all_params = $this->_tpl_vars;
		if ($parameters) {
			$all_params = array_merge($all_params, $parameters);
		}
		return $content = parent::render($name, $all_params);
	}

	

	/**
	 * Default to using Twig instead of PHP.
	 * 
	 * @param string $name Template name
	 * @param array $defaults Array of defaults
	 */
	public function splitTemplateName($name, array $defaults = array())
	{
		if (!isset($defaults['renderer']) OR !$defaults['renderer']) {
			$defaults['renderer'] = 'twig';
		}

		return parent::splitTemplateName($name, $defaults);
	}


	
	/**
	 * Reset vars back to nothing.
	 */
	public function resetTemplateVars()
	{
		$this->_tpl_vars = array();
	}

	/**
	 * Assign multiple variables to the shared template parameters.
	 *
	 * @param array $params k=>v array of vars
	 */
	public function assignMulti(array $params)
	{
		$this->_tpl_vars = array_merge($this->_tpl_vars, $params);
	}

	public function offsetExists($offset)
	{
		return isset($this->_tpl_vars[$offset]);
	}

	public function offsetSet($offset, $value)
	{
		$this->_tpl_vars[$offset] = $value;
	}

	public function offsetGet($offset)
	{
		return $this->_tpl_vars[$offset];
	}

	public function offsetUnset($offset)
	{
		unset($this->_tpl_vars[$offset]);
	}
}