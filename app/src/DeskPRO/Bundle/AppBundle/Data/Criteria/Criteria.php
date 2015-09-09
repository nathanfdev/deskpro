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

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class Criteria
 */
abstract class Criteria implements CriteriaInterface
{
    /**
     * @var array
     */
    protected $filters;

    /**
     * Criteria constructor.
     * @param array $filters
     */
    protected function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Create an instance from parameters
     *
     * This method is complicated for the sake of universality. It relies on duck-typing checks to handle creation
     * of different types of Criteria instances such as GroupableCriteriaInterface etc.
     *
     * @param array $params
     * @param OptionsResolver $resolver
     * @return Criteria
     */
    public static function fromParameters(array $params, OptionsResolver $resolver, array $data = [])
    {
        $isGroupable = in_array(GroupableCriteriaInterface::class, class_implements(static::class));

        static::configureResolver($resolver, $data);
        if ($isGroupable) {
            static::configureGroupByResolver($resolver);
        }

        $params = $resolver->resolve($params);

        if ($isGroupable) {
            $group_by = static::extractGroupBy($params);
        }

        $instance = new static($params);

        if ($isGroupable) {
            $instance->setGroupBy($group_by);
        }

        return $instance;
    }

    /**
     * @param QueryBuilder $qb
     */
    public abstract function applyFilters(QueryBuilder $qb);
}