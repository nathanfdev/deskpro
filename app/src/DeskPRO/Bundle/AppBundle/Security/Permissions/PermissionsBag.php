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
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Security\Permissions;

/**
 * The PermissionsBag acts like an immutable array, and also offers an API with methods like has('key') and get('key', 'default'). $permissions in this bag are the boolean yes/no permissions, but there are other methods for getting things like allowed departments, and allowed content categories.
 *
 * Using the hasPermission(name) method returns a bool: true if the bag has that permission and it is turned on, and false if not.
 */
class PermissionsBag implements \ArrayAccess, \IteratorAggregate, \Countable, \Serializable
{
    /**
     * @var array key is a permission name, value is to be interpreted as a boolean
     */
    protected $permissions;

    /**
     * @var array just a list of allowed department ids for tickets
     */
    protected $department_ticket_ids;

    /**
     * @var array just a list of allowed department ids for chat
     */
    protected $department_chat_ids;

    public function __construct(
        array $permissions = array(),
        $department_ticket_ids = array(),
        $department_chat_ids = array()
    )
    {
        $this->setArray($permissions);
        $this->setAllowedTicketDepartmentIds($department_ticket_ids);
        $this->setAllowedChatDepartmentIds($department_chat_ids);
    }

    /**
     * This is the primary method to ask for the yes/no boolean permissions (e.g. tickets.reopen_resolved)
     *
     * @param $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        if (!$this->has($permission)) {
            return false;
        }

        return (bool) $this->get($permission, false);
    }

    /**
     * A list of department IDs that are allowed for tickets
     *
     * @return array
     */
    public function getAllowedTicketDepartmentIds()
    {
        // the keys are the ids, and the value is an array of permissions like "full" => 1.
        // this is just for an array of the ids, but for "deeper" questions we will make another
        // method
        return array_keys($this->department_ticket_ids);
    }

    /**
     * A list of department IDs that are allowed in chat
     *
     * @return array
     */
    public function getAllowedChatDepartmentIds()
    {
        // the keys are the ids, and the value is an array of permissions like "full" => 1.
        // this is just for an array of the ids, but for "deeper" questions we will make another
        // method
        return array_keys($this->department_chat_ids);
    }

    public function setAllowedTicketDepartmentIds(array $department_ticket_ids)
    {
        $this->department_ticket_ids = $department_ticket_ids;
    }

    public function setAllowedChatDepartmentIds(array $chat_department_ids)
    {
        $this->department_chat_ids = $chat_department_ids;
    }

    /**
     * This is for the yes/no boolean permissions (e.g. tickets.reopen_resolved)
     *
     * You should probably use hasPermissions() above instead for a yes/no answer.
     *
     * @param $key
     * @param bool $default
     * @return bool
     */
    public function get($key, $default = false)
    {
        return (bool)($this->has($key) ? $this->permissions[$key] : $default);
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
