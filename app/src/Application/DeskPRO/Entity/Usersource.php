<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;

use Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\Usersource")
 * @ORM_Mapping\Table(name="usersources")
 */
class Usersource extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\GeneratedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * A note or description about the user source (admin eyes)
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="note", type="text")
	 */
	protected $note = '';

	/**
	 * The title of this usersource
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="title", type="string", length=255)
	 */
	protected $title = '';

	/**
	 * The type of usersource this is. This maps to an adapter class.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="source_type", type="string", length=255)
	 */
	protected $source_type;

	/**
	 * Options we'll pass to the adapter. These options should be set up with some installer.
	 *
	 * @var array
	 * @ORM_Mapping\Column(name="options", type="array")
	 */
	protected $options = array();

	/**
	 * The order in which to display this source
	 * @var int
	 * @ORM_Mapping\Column(name="display_order", type="integer")
	 */
	protected $display_order = 0;

	/**
	 * True if this usersource is enabled/usable.
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="is_enabled", type="boolean")
	 */
	protected $is_enabled = true;

	/**
	 * @var \Application\DeskPRO\Usersource\Adapter\AbstractAdapter
	 */
	protected $_adapter_instance = null;

	/**
	 * Get the usersource adapter for this usersource.
	 *
	 * @return \Application\DeskPRO\Usersource\Adapter\AbstractAdapter
	 */
	public function getAdapter()
	{
		if ($this->_adapter_instance !== null) {
			return $this->_adapter_instance;
		}

		switch ($this->source_type) {
			case 'facebook':
				$classname = 'Application\\DeskPRO\\Usersource\\Adapter\\Facebook';
				break;

			case 'google':
				$classname = 'Application\\DeskPRO\\Usersource\\Adapter\\Google';
				break;

			default:
				throw new \RuntimeException("Unknown usersource type `{$this->source_type}`");
				break;
		}

		$this->_adapter_instance = new $classname($this);

		return $this->_adapter_instance;
	}

	public function __call($name, $args)
	{
		return call_user_func_array(array($this->getAdapter(), $name), $args);
	}

	public function hasOption($name)
	{
		return isset($this->options[$name]);
	}

	public function getOption($name, $default = null)
	{
		return isset($this->options[$name]) ? $this->options[$name] : $default;
	}

	public function setOption($name, $value)
	{
		$this->options[$name] = $value;
	}

	public function setOptions(array $options, $reset = false)
	{
		if ($reset) {
			$this->options = $options;
		} else {
			$this->options = array_merge($this->options, $options);
		}
	}
}
