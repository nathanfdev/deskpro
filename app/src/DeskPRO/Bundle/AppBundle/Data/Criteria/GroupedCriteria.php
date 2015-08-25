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
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\Data\Criteria;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class GroupedCriteria
 */
abstract class GroupedCriteria extends Criteria implements GroupedCriteriaInterface
{
    /**
     * @var string
     */
    protected $group_by;

    /**
     * @param array $filters
     * @param string $group_by
     */
    public function __construct(array $filters, $group_by)
    {
        parent::__construct($filters);
        $this->group_by = $group_by;
    }

    /**
     * @return bool
     */
    public function hasGroupBy()
    {
        return (bool) $this->group_by;
    }

    /**
     * @return string
     */
    public function getGroupBy()
    {
        return $this->group_by;
    }

    /**
     * throws \LogicException
     */
    public function ensureGroupBy()
    {
        if (!$this->hasGroupBy()) {
            throw new \LogicException('Cannot group without group_by');
        }
    }

    /**
     * @param array $params
     * @param OptionsResolver $resolver
     * @param array $data
     * @return GroupedCriteria
     */
    public static function fromParameters(array $params, OptionsResolver $resolver, array $data = [])
    {
        static::configureResolver($resolver, $data);
        $params = $resolver->resolve($params);

        $group_by = null;
        if (array_key_exists('group_by', $params)) {
            $group_by = $params['group_by'];
            unset($params['group_by']);
        }

        $filters = $params;

        return new static($filters, $group_by);
    }

    /**
     * @todo remove this, let DataServices decide if they need to handle special cases
     *
     * If the current $group_by value leads to groups with distinct records (or distinct counts)
     *
     * When grouping by a related entity with to-Many relation (results are not distinct), resulting groups will
     * contain the same records in different groups, so e.g. when selecting grouped counts, the total sum of all
     * groups will be greater than the total actual count, thus we need to perform additional distinct COUNT().
     *
     * @return bool
     */
    public abstract function isGroupByDistinct();
}