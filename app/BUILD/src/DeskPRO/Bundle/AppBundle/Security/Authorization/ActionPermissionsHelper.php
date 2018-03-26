<?php

namespace DeskPRO\Bundle\AppBundle\Security\Authorization;

/*
 * Class ActionPermissionsHelper.
 */
use DeskPRO\Bundle\AppBundle\ApiTag\Model\Tag;
use DeskPRO\Bundle\AppBundle\ApiTag\TagsCollector;

/**
 * Class ActionPermissionsHelper.
 */
class ActionPermissionsHelper
{
    /**
     * @var TagsCollector
     */
    private $tagsCollector;

    /**
     * ActionPermissionsHelper constructor.
     *
     * @param TagsCollector $tagsCollector
     */
    public function __construct(TagsCollector $tagsCollector)
    {
        $this->tagsCollector = $tagsCollector;
    }

    /**
     * @param $action_tags
     * @param $gathered_tags
     *
     * @return array|mixed
     */
    public function calculateAccess($action_tags, $gathered_tags)
    {

        // just a little optimisation - no need to calculate something if everything is allowed/denied
        if (in_array('*', $gathered_tags)) {
            return true;
        } elseif (in_array('-*', $gathered_tags)) {
            return false;
        }

        $hierarchy = $this->tagsCollector->getTagsHierarchy($action_tags);
        foreach ($hierarchy as $hierarchyItem) {
            $this->tagsCollector->populateByGathered($gathered_tags, $hierarchyItem);
        }

        $result = array_reduce($hierarchy, [$this, 'reduce']);
        $this->tagsCollector->resetHierarchy();

        return $result;
    }

    /**
     * Reduces whole the hierarchy tree for current method to just one boolean value.
     * It's simple. Access will be granted if and only all tags are allowed.
     *
     * @param null|bool $permit
     * @param Tag       $item
     *
     * @return bool|mixed
     */
    protected function reduce($permit, $item)
    {
        return $item->hasNodes() ? array_reduce($item->getNodes(), [$this, 'reduce'], $permit) : $this->calculatePermission($permit, $item);
    }

    /**
     * @param null|bool $permit
     * @param Tag       $item
     *
     * @return bool
     */
    protected function calculatePermission($permit, $item)
    {
        if ($permit === null) {
            return $item->getValue() === null ? null : $item->getValue() > 0;
        } elseif ($item->getValue() === null && $permit === true) {
            return $permit;
        } else {
            return $permit && $item->getValue() > 0;
        }
    }
}
