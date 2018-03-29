<?php

/**
 * DeskPRO.
 *
 * @category Tickets
 */

namespace Application\DeskPRO\People\PermissionLoader;

use Application\DeskPRO\App;

/**
 * A generic category loader.
 */
abstract class BasicTreeCategoryPermission extends BasicCategoryPermission
{
    /**
     * An array of specific categories allowed by actual database records.
     *
     * @var array
     */
    protected $specific_cats = [];

    protected function init()
    {
        $this->specific_cats = App::getEntityRepository($this->getCategoryEntity())->getCategoriesForUsergroups($this->getUsergroupIds());
        $full                = App::getEntityRepository($this->getCategoryEntity())->getRootNodes();

        $this->_computeTree($full);

        $all_ids               = App::getEntityRepository($this->getCategoryEntity())->getIds();
        $this->disallowed_cats = array_diff($all_ids, $this->allowed_cats);
    }

    protected function _computeTree($tree, $default = null)
    {
        foreach ($tree as $node) {
            if ($default or in_array($node['id'], $this->specific_cats)) {
                $this->allowed_cats[] = $node['id'];

                if ($node['children']) {
                    $this->_computeTree($node['children']);
                }
            }
        }
    }

    /**
     * Get an array of specific categories allowed as defined by the db.
     * This is before inheritance is considered.
     *
     * @return array
     */
    public function getSpecificCategories()
    {
        return $this->specific_cats;
    }

    /**
     * Get an array of data we'll serialize.
     *
     * @return array
     */
    protected function serializeData()
    {
        $data                  = parent::serializeData();
        $data['specific_cats'] = $this->specific_cats;

        return $data;
    }

    /**
     * Initialize this object with an array of saved data.
     *
     * @param array $data
     */
    protected function unserializeData(array $data)
    {
        parent::unserializeData($data);
        $this->specific_cats = $data['specific_cats'];
    }
}
