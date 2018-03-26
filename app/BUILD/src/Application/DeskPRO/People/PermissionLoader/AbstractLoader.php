<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\People\PermissionLoader;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;

/**
 * A permission loader knows how to load permissions for a thing.
 */
abstract class AbstractLoader implements \Serializable
{
    /**
     * @var int[]
     */
    protected $usergroup_ids;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @var int
     */
    protected $person_id = 0;

    /**
     * @var string
     */
    public $loaded_key = '';

    public function getSubkey()
    {
        return;
    }

    public function setPersonContext(Person $person)
    {
        $this->person    = $person;
        $this->person_id = $person->id;
    }

    /**
     * @param int[]                              $usergroup_ids
     * @param \Application\DeskPRO\Entity\Person $person        Optional person to fetch overrides for
     */
    public function __construct(array $usergroup_ids, Person $person = null)
    {
        $this->usergroup_ids = $usergroup_ids;

        $everyoneGroup = App::$container->getUserGroups()->getEveryoneGroup(false);
        if ($everyoneGroup && $everyoneGroup->isEnabled()) {
            $this->usergroup_ids[] = 1;
        } else {
            $this->usergroup_ids[] = 0;
        }
        $this->usergroup_ids = array_unique($this->usergroup_ids);
        sort($this->usergroup_ids, \SORT_NUMERIC);

        $this->person = $person;

        $this->init();
    }

    protected function init()
    {
    }

    /**
     * Get the usergroup IDs represented by the loaded permissions.
     *
     * @return array
     */
    public function getUsergroupIds()
    {
        return $this->usergroup_ids;
    }

    /**
     * Get an array of data we'll serialize.
     *
     * @return array
     */
    abstract protected function serializeData();

    public function serialize()
    {
        $data                  = $this->serializeData();
        $data['usergroup_ids'] = $this->usergroup_ids;

        return serialize($data);
    }

    /**
     * Initialize this object with an array of saved data.
     *
     * @param array $data
     */
    abstract protected function unserializeData(array $data);

    public function unserialize($data)
    {
        $data = unserialize($data);

        $this->usergroup_ids = $data['usergroup_ids'];
        $this->unserializeData($data);
    }
}
