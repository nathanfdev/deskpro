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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\Data\Criteria;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class Criteria.
 */
abstract class Criteria implements CriteriaInterface
{
    /**
     * @var array
     */
    protected $filters;

    /**
     * Criteria constructor.
     *
     * @param array $filters
     */
    protected function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Create an instance from parameters.
     *
     * This method is complicated for the sake of universality. It relies on duck-typing checks to handle creation
     * of different types of Criteria instances such as GroupableCriteriaInterface, SortableCriteriaInterface etc.
     *
     * @param array           $params
     * @param OptionsResolver $resolver
     * @param array           $data
     *
     * @return Criteria
     */
    public static function fromParameters(array $params, OptionsResolver $resolver, array $data = [])
    {
        $is_groupable = in_array(GroupableCriteriaInterface::class, class_implements(static::class));
        $is_sortable  = in_array(SortableCriteriaInterface::class, class_implements(static::class));

        static::configureResolver($resolver, $data);
        if ($is_groupable) {
            static::configureGroupByResolver($resolver);
        }
        if ($is_sortable) {
            static::configureSortingResolver($resolver);
        }

        $params = $resolver->resolve($params);

        if ($is_groupable) {
            $group_by = static::extractGroupBy($params);
        }
        if ($is_sortable) {
            list($sort, $order) = static::extractSorting($params);
        }

        $instance = new static($params);

        if ($is_groupable) {
            $instance->setGroupBy($group_by);
        }
        if ($is_sortable) {
            $instance->setSort($sort);
            $instance->setOrder($order);
        }

        return $instance;
    }
}
