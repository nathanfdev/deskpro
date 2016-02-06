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
class ActionPermissionsHelper
{
    /**
     * @param $action_tags
     * @param $gathered_tags
     *
     * @return array|mixed
     */
    public function calculateAccess($action_tags, $gathered_tags)
    {
        $action_tags_hierarchy   = call_user_func_array('array_merge_recursive', array_map([$this, 'createActionTagsHierarchy'], $action_tags));
        $gathered_tags_hierarchy = $gathered_tags ? call_user_func_array('array_merge_recursive', array_map([$this, 'createGatheredTagsHierarchy'], $gathered_tags)) : [];

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
        return $this->createTagsHierarchy($tags, false);
    }

    /**
     * @param $tags
     *
     * @return mixed
     */
    protected function createGatheredTagsHierarchy($tags)
    {
        return $this->createTagsHierarchy($tags);
    }

    /**
     * @param $tags
     * @param null $base_permission
     *
     * @return mixed
     */
    protected function createTagsHierarchy($tags, $base_permission = null)
    {
        $permit    = is_null($base_permission) ? !(0 === strpos($tags, '-')) : $base_permission;
        $tags      = explode('.', str_replace('-', '', $tags));
        $hierarchy = $this->recursion([], $tags, $permit);

        return $hierarchy;
    }

    /**
     * @param $hierarchy
     * @param $tags
     * @param $permit
     *
     * @return mixed
     */
    protected function recursion($hierarchy, $tags, $permit)
    {
        $tag = array_shift($tags);
        if ($tags) {
            $hierarchy[$tag] = $this->recursion([], $tags, $permit);
        } else {
            $hierarchy[$tag] = $permit;
        }

        return $hierarchy;
    }

    /**
     * @param $permit
     * @param $item
     *
     * @return bool|mixed
     */
    protected function reduce($permit, $item)
    {
        return is_array($item) ? array_reduce($item, [$this, 'reduce'], $permit) : ($permit !== null ? $permit && $item : $item);
    }
}
