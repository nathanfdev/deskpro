<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;

class UsergroupDataService extends BaseRepositoryService
{
    /**
     * @var bool
     */
    protected $has_init = false;

    /**
     * @var \Application\DeskPRO\Entity\Usergroup[]
     */
    protected $ugs;

    /**
     * @var \Application\DeskPRO\Entity\Usergroup[]
     */
    protected $agent_ugs;

    /**
     * @var \Application\DeskPRO\Entity\Usergroup[]
     */
    protected $user_ugs;

    /**
     * @var int[]
     */
    protected $ug_ids = [];

    /**
     * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    protected $continer;

    /**
     * {@inheritdoc}
     */
    public static function create(DeskproContainer $container, array $options = null)
    {
        if (!$options) {
            $options = [];
        }
        $options['entity']    = 'Application\\DeskPRO\\Entity\\Usergroup';
        $options['container'] = $container;

        $em = $container->getEm();
        $o  = new static($em, $options);

        return $o;
    }

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        $this->continer = $this->options['container'];
    }

    /**
     * Loads data.
     */
    protected function preload()
    {
        if ($this->has_init) {
            return;
        }
        $this->has_init = true;

        $this->ugs = $this->em->createQuery('
            SELECT ug
            FROM DeskPRO:Usergroup ug INDEX BY ug.id
            ORDER BY ug.id ASC
        ')->execute();

        foreach ($this->ugs as $ug) {
            $this->ug_ids[] = $ug->getId();
            $ug->getTitle();

            if ($ug->is_agent_group) {
                $this->agent_ugs[$ug->getId()] = $ug;
            } else {
                $this->user_ugs[$ug->getId()] = $ug;
            }
        }
    }

    /**
     * Gets a usergroup (either user or agent).
     *
     * @param int $ug_id
     *
     * @return \Application\DeskPRO\Entity\Usergroup|null
     */
    public function get($ug_id)
    {
        $this->preload();

        return isset($this->ugs[$ug_id]) ? $this->ugs[$ug_id] : null;
    }

    /**
     * @param int $id
     *
     * @return \Application\DeskPRO\Entity\Usergroup|null
     */
    public function getAgentGroup($id)
    {
        $this->preload();

        return isset($this->agent_ugs[$id]) ? $this->agent_ugs[$id] : null;
    }

    /**
     * @param int $id
     *
     * @return \Application\DeskPRO\Entity\Usergroup|null
     */
    public function getUserGroup($id)
    {
        $this->preload();

        return isset($this->user_ugs[$id]) ? $this->user_ugs[$id] : null;
    }

    /**
     * Gets all groups.
     *
     * @return array
     */
    public function getAll()
    {
        $this->preload();

        return $this->ugs;
    }

    /**
     * Gets an array of user groups.
     *
     * @return array
     */
    public function getUserUsergroups()
    {
        $this->preload();

        return $this->user_ugs;
    }

    /**
     * Gets an array of agent groups.
     */
    public function getAgentUsergroups()
    {
        $this->preload();

        return $this->agent_ugs;
    }

    /**
     * @param null $for_ids
     *
     * @return array
     */
    public function getNames($for_ids = null)
    {
        $this->preload();

        $ret = [];

        if ($for_ids) {
            foreach ($for_ids as $cid) {
                if (!$this->get($cid)) {
                    $ret[$cid] = "Unknown #$cid";
                } else {
                    $ret[$cid] = $this->get($cid)->title;
                }
            }
        } else {
            foreach ($this->ug_ids as $cid) {
                $ret[$cid] = $this->get($cid)->title;
            }
        }

        return $ret;
    }

    /**
     * @return array
     */
    public function getUsergroupNames()
    {
        $this->preload();
        $names = $this->getNames(array_keys($this->user_ugs));

        return $names;
    }

    /**
     * @return array
     */
    public function getAgentUsergroupNames()
    {
        return $this->getUsergroupNames(array_keys($this->agent_ugs));
    }

    /**
     * @param array $ids
     * @param bool  $keep_order
     *
     * @return array
     */
    public function getByIds(array $ids, $keep_order = false)
    {
        $this->preload();
        $ret = [];

        foreach ($ids as $id) {
            if (isset($this->ugs[$id])) {
                $ret[$id] = $this->ugs[$id];
            }
        }

        return $ret;
    }

    /**
     * Pass-through to repository.
     */
    public function __call($method, array $args = [])
    {
        $this->preload();

        return parent::__call($method, $args);
    }
}
