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
 * @subpackage Permissions
 */

namespace Application\AuthBundle\Permissions;

/**
 * The PermissionsBag acts like an immutable array, and also offers an API with methods like has('key') and get('key', 'default').
 *
 * Using the hasPermission(name) method returns a bool if the bag has that permission and it is turned on, or not
 */
class PermissionsBag implements \ArrayAccess, \IteratorAggregate, \Countable, \Serializable
{
    /**
     * @var array
     */
    private $permissions;


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
        return $this->permissions;
    }

    public function setArray(array $permissions)
    {
        $this->permissions = $permissions;
    }

    public function has($key)
    {
        return array_key_exists($key, $this->permissions);
    }

    public function get($key, $default = null)
    {
        return $this->has($key) ? $this->permissions[$key] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->permissions);
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
        throw new \LogicException('cannot set a permission in this way. instead, change the underlying entities in the permission system. this is just a dumb bag of data.');
    }


    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        throw new \LogicException(
            'cannot set a permission in this way. instead, change the underlying entities in the permission system. this is just a dumb bag of data.'
        );
    }


    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize($this->permissions);
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
        return count($this->permissions);
    }
}
