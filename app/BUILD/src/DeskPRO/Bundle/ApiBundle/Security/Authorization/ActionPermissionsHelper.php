<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Security\Authorization;

/**
 * Class ActionPermissionsHelper.
 */
/**
 * Class ActionPermissionsHelper.
 */
class ActionPermissionsHelper
{
    /**
     * @param $action_tags
     * @param $gathered_tags
     * Please don't ask me how does it work
     *
     * @return array|mixed
     */
    public function calculateAccess($action_tags, $gathered_tags)
    {
        $action_tags_hierarchy   = call_user_func_array('array_merge_recursive', array_map([$this, 'createActionTagsHierarchy'], $action_tags));
        $gathered_tags_hierarchy = $gathered_tags ? call_user_func_array('array_merge_recursive', array_map([$this, 'createGatheredTagsHierarchy'], $gathered_tags)) : [];

        //temporary allow/deny all restrictions
        if (in_array('*', $gathered_tags)) {
            return true;
        } elseif (in_array('-*', $gathered_tags)) {
            return false;
        }

        $result = array_merge($action_tags_hierarchy, $gathered_tags_hierarchy);

        $result = array_reduce($result, [$this, 'reduce']);

        return $result;
    }

    /**
     * @param $tags
     *
     * @return mixed
     */
    protected function createActionTagsHierarchy($tags)
    {
        return $this->createTagsHierarchy($tags);
    }

    /**
     * @param $tags
     *
     * @return mixed
     */
    protected function createGatheredTagsHierarchy($tags)
    {
        return $this->createTagsHierarchy($tags, true);
    }

    /**
     * null means deny by default
     * false meet strict deny
     * true means allow.
     *
     * @param $tags
     * @param bool $calc_permission
     *
     * @return mixed
     */
    protected function createTagsHierarchy($tags, $calc_permission = false)
    {
        $permit    = $calc_permission ? !(0 === strpos($tags, '-')) : null;
        $tags      = explode('.', str_replace('-', '', $tags));
        $hierarchy = $this->recursion($tags, $permit);

        return $hierarchy;
    }

    /**
     * This method just inflate hierarchy tree. E.g.
     *     'test.test2.test3' becomes
     *      'test' => [
     *          'test1' => [
     *              'test3' => true
     *              ],
     *          ],
     *      ].
     *
     * @param $tags
     * @param $permit
     *
     * @return mixed
     */
    protected function recursion($tags, $permit)
    {
        $hierarchy = [];
        $tag       = array_shift($tags);
        if ($tags) {
            $hierarchy[$tag] = $this->recursion($tags, $permit);
        } else {
            $hierarchy[$tag] = $permit;
        }

        return $hierarchy;
    }

    /**
     * @param $permit
     * @param $item
     * Reduces whole the hierarchy tree for current method to just one boolean value.
     * It's simple. Access will be granted if and only all tags are allowed.
     *
     * @return bool|mixed
     */
    protected function reduce($permit, $item)
    {
        return is_array($item) ? array_reduce($item, [$this, 'reduce'], $permit) : $this->calculatePermission($permit, $item);
    }

    /**
     * @param $permit
     * @param $item
     *
     * @return bool
     */
    protected function calculatePermission($permit, $item)
    {
        if ($permit === null) {
            return $item;
        } elseif ($item === null && $permit === true) {
            return $permit;
        } else {
            return $permit && $item;
        }
    }
}
