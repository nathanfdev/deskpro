<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Publish\Feedback;

use Application\DeskPRO\App;
use Orb\Util\Arrays;

class GroupingCounter
{
    /** @var string */
    protected $grouping1 = 'status';
    /** @var string */
    protected $grouping2 = 'category_id';
    /** @var int|null */
    protected $this_person_id = null;
    /** @var array */
    protected $terms = [];
    /** @var array|null */
    protected $ids = null;

    /**
     * Set specific IDs we want to group.
     *
     * @param array $ids
     */
    public function setIds(array $ids)
    {
        $this->ids = array_map('intval', $ids);
    }

    /**
     * Get an array of counts suitable for looping in a template etc.
     *
     * @return array
     */
    public function getDisplayArray()
    {
        //------------------------------
        // Connect counts to titles
        //------------------------------

        $display_elements = $this->getDisplayElementsArray();
        $titles1          = $display_elements['titles1'];
        $titles2          = $display_elements['titles2'];
        $counts           = $display_elements['counts'];

        Arrays::unshiftAssoc($titles1, -1, 'TOTAL');
        if ($titles2) {
            Arrays::unshiftAssoc($titles2, -1, 'TOTAL');
        }

        $items = [];

        $group1_has = [];
        $group2_has = [];

        foreach ($titles1 as $field1_id => $field1_title) {
            if (!isset($counts[$field1_id])) {
                continue;
            }

            $countinfo = $counts[$field1_id];

            $group1_has[] = $field1_id;

            $row          = [];
            $row['id']    = $field1_id;
            $row['title'] = $field1_title;
            $row['total'] = $countinfo['total'];

            if (!empty($countinfo['sub'])) {
                $row['sub'] = [];
                foreach ($titles2 as $field2_id => $field2_title) {
                    if (!isset($countinfo['sub'][$field2_id])) {
                        continue;
                    }
                    $countinfo2 = $countinfo['sub'][$field2_id];

                    $group2_has[] = $field2_id;

                    $row2          = [];
                    $row2['id']    = $field2_id;
                    $row2['title'] = $field2_title;
                    $row2['total'] = !empty($countinfo2['total']) ? $countinfo2['total'] : 0;

                    $row['sub'][$field2_id] = $row2;
                }
            }

            $items[$field1_id] = $row;
        }

        //------------------------------
        // Now fetch hierarchy which might be used
        //------------------------------

        $group1_structure = [];
        $group2_structure = [];

        $status_hierarchy = function () {
            $titles = [
                'new'    => ['title' => 'New'],
                'active' => ['title' => 'Active', 'children' => []],
                'closed' => ['title' => 'Closed', 'children' => []],
                'hidden' => ['title' => 'Hidden'],
            ];

            $active_status_cats = App::getEntityRepository('DeskPRO:FeedbackStatusCategory')->getActiveCategories();
            $closed_status_cats = App::getEntityRepository('DeskPRO:FeedbackStatusCategory')->getClosedCategories();

            foreach ($active_status_cats as $cat) {
                $titles['active.'.$cat['id']]                       = ['title' => $cat['title']];
                $titles['active']['children']['active.'.$cat['id']] = ['title' => $cat['title']];
            }
            foreach ($closed_status_cats as $cat) {
                $titles['closed.'.$cat['id']]                       = ['title' => $cat['title']];
                $titles['closed']['children']['closed.'.$cat['id']] = ['title' => $cat['title']];
            }
        };

        switch ($this->grouping1) {
            case 'category_id':
                $group1_structure = App::getEntityRepository('DeskPRO:FeedbackCategory')->getFullNames();
                break;

            case 'status':
                $group1_structure = $status_hierarchy();
                break;

            default:
                foreach ($titles1 as $id => $t) {
                    $group1_structure[$id] = ['title' => $t];
                }
                break;
        }

        if ($this->grouping2) {
            switch ($this->grouping2) {
                case 'category_id':
                    $group1_structure = App::getEntityRepository('DeskPRO:FeedbackCategory')->getFullNames();
                    break;

                case 'status':
                    $group1_structure = $status_hierarchy();
                    break;

                default:
                    foreach ($titles2 as $id => $t) {
                        $group2_structure[$id] = ['title' => $t];
                    }
                    break;
            }
        }

        return [
            'items'            => $items,
            'group1_structure' => $group1_structure,
            'group2_structure' => $group2_structure,
        ];
    }

    /**
     * Sort a display array so that the biggest counts are first.
     *
     * @param array $display_array
     */
    public function sortDisplayArray(array &$display_array)
    {
        uasort($display_array, [$this, '_sortDisplayArrayCallback']);
    }

    public function _sortDisplayArrayCallback($a, $b)
    {
        if ($a['total'] == $b['total']) {
            return 0;
        }

        return ($a['total'] < $b['total']) ? -1 : 1;
    }

    /**
     * Get the raw counts.
     *
     * @return array
     */
    public function getCounts()
    {
        $group_by = 'GROUP BY field1';

        $grouping1 = $this->grouping1;
        $grouping2 = $this->grouping2;
        $db        = App::getDb();

        if ($grouping1 == 'status') {
            $grouping1 = "IF(feedback.status_category_id, CONCAT(feedback.status, '.', feedback.status_category_id), feedback.status)";
        } else {
            $grouping1 = $db->quoteIdentifier('feedback.'.$grouping1);
        }

        if ($grouping2 == 'status') {
            $grouping2 = "IF(feedback.status_category_id, CONCAT(feedback.status, '.', feedback.status_category_id), feedback.status)";
        } else {
            $grouping2 = $db->quoteIdentifier('feedback.'.$grouping2);
        }

        $select_fields[] = "COALESCE($grouping1, 0) AS field1";
        if ($this->grouping2) {
            $select_fields[] = "COALESCE($grouping2, 0) AS field2";
            $group_by .= ', field2';
        }
        $select_fields[] = 'COUNT(*) AS total';

        $where = "WHERE (feedback.status != 'hidden')";
        if (is_array($this->ids)) {
            if (empty($this->ids)) {
                return [];
            }

            $where = 'WHERE feedback.id IN('.implode(',', $this->ids).')';
        }

        $sql = '
            SELECT '.implode(', ', $select_fields)."
            FROM feedback
            $where
            $group_by WITH ROLLUP
        ";

        $counts = $db->fetchAll($sql);

        return $counts;
    }

    /**
     * Get information about strucutred counts and titles.
     *
     * @return array
     */
    public function getDisplayElementsArray()
    {
        $counts = $this->getCounts();

        //------------------------------
        // Get titles for each grouping, and sort into a keyed structure
        //------------------------------

        $ids1 = [];
        if ($this->grouping2) {
            $ids2 = [];
        }

        // $counts_structure becomes:
        // array(field1 => array(total => xxx, sub => array(someid => 123, someid2 => 123 ...) )

        $counts_structured = [];
        foreach ($counts as $count) {
            // Store ID's
            if ($count['field1'] !== null) {
                $ids1[] = $count['field1'];
            }

            if ($this->grouping2 and $count['field2'] !== null) {
                $ids2[] = $count['field2'];
            }

            //------------------------------
            // Into structure
            //------------------------------

            // Set ROLLUP's (totals) to -1
            if ($count['field1'] === null) {
                $count['field1'] = -1;
            }
            if ($this->grouping2 and $count['field2'] === null) {
                $count['field2'] = -1;
            }

            // Init array keys
            if (!isset($counts_structured[$count['field1']])) {
                $counts_structured[$count['field1']] = ['total' => $count['total']];
                if ($this->grouping2) {
                    $counts_structured[$count['field1']]['sub'] = [];
                }
            }

            // Save numbers
            if ($this->grouping2) {
                if ($count['field2'] == -1) {
                    $counts_structured[$count['field1']]['total'] = $count['total'];
                } else {
                    $counts_structured[$count['field1']]['sub'][$count['field2']] = $count['total'];
                }
            }
        }

        $ids1 = array_unique($ids1);

        if ($this->grouping2) {
            $ids2 = array_unique($ids2);
        }

        $titles1 = $this->getFieldTitles($this->grouping1, $ids1);
        $titles2 = null;
        if ($this->grouping2) {
            $titles2 = $this->getFieldTitles($this->grouping2, $ids2);
        }

        return [
            'titles1' => $titles1,
            'titles2' => $titles2,
            'counts'  => $counts_structured,
        ];
    }

    /**
     * Get a string of id=>title for a particular field, given IDs.
     * Sometimes $ids is not needed (ie departments can all be fetched),
     * other times it's important (ie dont want every company name in the entire db).
     *
     * @param string $field
     * @param array  $ids
     *
     * @return array
     */
    public function getFieldTitles($field, array $ids = null)
    {
        $titles = null;
        switch ($field) {
            case 'category_id':
                $titles = App::getOrm()->getRepository('DeskPRO:FeedbackCategory')->getFullNames();
                Arrays::unshiftAssoc($titles, 0, App::getTranslator()->phrase('agent.general.none'));
                break;

            case 'status':

                $titles = [
                    'new'    => 'New',
                    'active' => 'Active',
                    'closed' => 'Closed',
                    'hidden' => 'Hidden',
                ];

                $active_status_cats = App::getEntityRepository('DeskPRO:FeedbackStatusCategory')->getActiveCategories();
                $closed_status_cats = App::getEntityRepository('DeskPRO:FeedbackStatusCategory')->getClosedCategories();

                foreach ($active_status_cats as $cat) {
                    $titles['active.'.$cat['id']] = 'Active > '.$cat['title'];
                }
                    foreach ($closed_status_cats as $cat) {
                        $titles['closed.'.$cat['id']] = 'Closed > '.$cat['title'];
                    }

                return $titles;

                break;

            default:
                // Just make all titles the ids themselves by default,
                // useful for things like status which might be rendered into words after
                if ($ids) {
                    $titles = array_combine($ids, $ids);
                } else {
                    $titles = [];
                }
                break;
        }

        return $titles;
    }

    /**
     * Set the grouping fields.
     *
     * @param string $grouping1
     * @param string $grouping2
     *
     * @return $this
     */
    public function setGrouping($grouping1, $grouping2 = null)
    {
        $this->grouping1 = $grouping1 ? $grouping1 : 'category';
        $this->grouping2 = $grouping2;

        return $this;
    }
}
