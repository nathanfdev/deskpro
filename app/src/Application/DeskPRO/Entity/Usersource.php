<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
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
