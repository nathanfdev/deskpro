<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Templating;

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
		$all_params = $this->getTemplateVarsObject()->getArrayCopy();

		if ($parameters) {
			$all_params = array_merge($all_params, $parameters);
		}

		return $content = parent::render($name, $all_params);
	}



	/**
	 * Get the templatevars object
	 *
	 * @return ArrayObject
	 */
	public function getTemplateVarsObject()
	{
		if ($this->_tpl_vars === null) {
			$this->_tpl_vars = new \ArrayObject();
		}

		return $this->_tpl_vars;
	}



	/**
	 * Reset vars back to nothing.
	 */
	public function resetTemplateVars()
	{
		$this->getTemplateVarsObject()->exchangeArray(array());
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