<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package    DeskPRO
 * @subpackage NewSettings
 */

namespace Application\AuthBundle\Permissions;

/**
 * The SettingsBag acts like an immutable array, and also offers an API with methods like has('key') and get('key', 'default').
 *
 */
class PermissionsBag implements \ArrayAccess, \IteratorAggregate, \Countable, \Serializable
{
    /**
     * @var array
     */
    private $settings;


    public function __construct(array $settings = array())
    {
        $this->setArray($settings);
    }


    public function hasPermission($permission)
    {
        if (!$this->has($permission)) {
            return false;
        }

        return (bool) $this->get($permission, false);
    }


    public function toArray()
    {
        return $this->settings;
    }

    public function setArray(array $settings)
    {
        $this->settings = $settings;
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


    /**
     * Seperating setting names by dots "." is popular. We can use grouping to subset a settings bag and get the
     * result array of settings that fit inside that subset.
     *
     * eg. new SettingsBag(array('core.register' => 1));
     *
     * This method with the input $group = 'core' will return:
     *
     * array('register' => 1)
     *
     * Set the "short" flag to false to not cut off the group part of the setting name, getting:
     *
     * array('core.register' => 1)
     *
     * @param  string $group the group prefix
     * @param  bool   $short true to cut the group name out of the result array keys
     * @return array
     */
    public function getGroup($group, $short = true)
    {
        $ret = array();

        $group_dot = $group.'.';
        $len = strlen($group_dot);

        foreach ($this->settings as $k => $v) {
            if (substr($k, 0, $len) === $group_dot) {
                $ret[$short ? substr($k, $len) : $k] = $v;
            }
        }

        return $ret;
    }
}
