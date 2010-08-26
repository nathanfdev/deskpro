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
class Engine extends \Symfony\Bundle\FrameworkBundle\Templating\Engine
{
	/**
	 * An array of shared template vars
	 * @var \ArrayObject
	 */
	protected $_tpl_vars = null;


	public function render($name, array $parameters = array())
	{
		if ($this->_tpl_vars) {
			$all_params = $this->getTemplateVarsObject()->getArrayCopy();
		} else {
			$all_params = array();
		}
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
	 * Get the templatevars object
	 *
	 * @return ArrayObject
	 */
	public function getTemplateVarsObject()
	{
		if ($this->_tpl_vars === null) {
			$this->resetTemplateVars();
		}

		return $this->_tpl_vars;
	}



	/**
	 * Reset vars back to nothing.
	 */
	public function resetTemplateVars()
	{
		if ($this->_tpl_vars !== null) {
			$this->getTemplateVarsObject()->exchangeArray(array());
		}
	}



	/**
	 * Assign multiple variables to the shared template parameters.
	 *
	 * @param array $params k=>v array of vars
	 */
	public function assignMulti(array $params)
	{
		$arr = $this->getTemplateVarsObject();
		foreach ($params as $k => $v) {
			$arr[$k] = $v;
		}
	}
}