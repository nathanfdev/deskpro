<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Actions;

use Orb\Util\CheckedOptionsArray;
use Orb\Util\OptionsArray;
use Orb\Util\Util;

/**
 * Base class for action defs. Each action still needs to implement
 * ActionInterface and/or MacroActionInterface interfaces.
 */
abstract class AbstractAction implements ActionDefinitionInterface
{
	/**
	 * @var \Orb\Util\OptionsArray
	 */
	private $options;

	/**
	 * @var \Orb\Util\OptionsArray
	 */
	private $meta;


	/**
	 * @param array  $options
	 */
	public function __construct(array $options = array())
	{
		$this->meta = new OptionsArray();
		$this->_initOptions($options);
	}


	/**
	 * @return OptionsArray
	 */
	public function getMetaData()
	{
		return $this->meta;
	}


	/**
	 * @param array $options
	 */
	private function _initOptions(array $options)
	{
		$this->options = $this->getOptionsDef();
		$this->options->setAll($options);
		$this->options->setArrayDefault($this->getDefaultOptions());
		$this->options->ensureRequired();
	}


	/**
	 * @return array
	 */
	protected function getDefaultOptions()
	{
		return array();
	}


	/**
	 * @return CheckedOptionsArray
	 */
	protected function getOptionsDef()
	{
		return new CheckedOptionsArray();
	}


	/**
	 * Gets the type name of the criteria
	 *
	 * @return string
	 */
	public function getActionType()
	{
		return Util::getBaseClassname($this);
	}


	/**
	 * Get's an array of options
	 *
	 * @return array
	 */
	public function getActionOptions()
	{
		return $this->options;
	}


	/**
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getActionOption($name, $default = null)
	{
		return isset($this->options[$name]) ? $this->options[$name] : $default;
	}
}