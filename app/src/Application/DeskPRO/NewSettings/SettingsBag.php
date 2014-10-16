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
 * @subpackage NewSettings
 */

namespace Application\DeskPRO\NewSettings;

/**
 * The SettingsBag acts like an immutable array, and also offers an API with methods like has('key') and get('key', 'default').
 *
 */
class SettingsBag implements \ArrayAccess, \IteratorAggregate, \Countable, \Serializable
{
	/**
	 * @var array
	 */
	private $settings;


	public function __construct(array $settings = array())
	{
		$this->settings = $settings;
	}


	public function toArray()
	{
		return $this->settings;
	}

	public function has($key)
	{
		return array_key_exists($key, $this->settings);
	}

	public function get($key, $default = null)
	{
		return $this->has($key) ? $this->settings[$key] : $default;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->settings);
	}


	/**
	 * {@inheritdoc}
	 */
	public function offsetExists($offset)
	{
		return $this->has($offset);
	}


	/**
	 * {@inheritdoc}
	 */
	public function offsetGet($offset)
	{
		return $this->get($offset);
	}


	/**
	 * {@inheritdoc}
	 */
	public function offsetSet($offset, $value)
	{
		throw new \LogicException('cannot set a setting in this way. instead, change the underlying source of the setting and get a fresh settings bag by forcing a reload of settings on the settings resolver');
	}


	/**
	 * {@inheritdoc}
	 */
	public function offsetUnset($offset)
	{
		throw new \LogicException(
			'cannot unset a setting in this way. instead, remove from the underlying source of the setting and get a fresh settings bag by forcing a reload of settings on the settings resolver'
		);
	}


	/**
	 * {@inheritdoc}
	 */
	public function serialize()
	{
		return serialize($this->settings);
	}


	/**
	 * {@inheritdoc}
	 */
	public function unserialize($serialized)
	{
		return unserialize($serialized);
	}


	/**
	 * {@inheritdoc}
	 */
	public function count()
	{
		return count($this->settings);
	}
}
 