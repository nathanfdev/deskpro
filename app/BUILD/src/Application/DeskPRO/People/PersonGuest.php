<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\People;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A website visitor when we have no information about them.
 */
class PersonGuest extends Person
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->id             = 0;
        $this->_usergroup_ids = [];
        $this->usergroups     = new ArrayCollection();
        $this->teams          = new ArrayCollection();
        $this->tickets        = new ArrayCollection();
        $this->chats          = new ArrayCollection();
        $this->timezone       = App::getSetting('core.default_timezone');
        $this->custom_data    = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getUsergroups()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function isGuest()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return ['ROLE_GUEST'];
    }

    public function noPersist()
    {
        throw new \BadMethodCallException('A PersonGuest cannot be persisted');
    }
}
