<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
