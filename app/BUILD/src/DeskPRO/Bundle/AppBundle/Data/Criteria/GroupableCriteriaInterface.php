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

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Interface GroupableCriteriaInterface.
 */
interface GroupableCriteriaInterface extends CriteriaInterface
{
    /**
     * @return array
     */
    public function getGroupByAllowedValues();

    /**
     * @param QueryBuilder $qb
     */
    public function applyGroupBy(QueryBuilder $qb);

    /**
     * Get group_by.
     *
     * @return string
     */
    public function getGroupBy();

    /**
     * Set group_by.
     *
     * @param string $value
     */
    public function setGroupBy($value);

    /**
     * If group_by is set.
     *
     * @return bool
     */
    public function hasGroupBy();

    /**
     * Ensures group_by is set.
     *
     * @throws \LogicException
     */
    public function ensureGroupBy();

    /**
     * Removes group_by from given parameters and returns the value of group_by.
     *
     * @param array $params
     *
     * @return string
     */
    public static function extractGroupBy(array &$params);

    /**
     * @param OptionsResolver $resolver
     */
    public static function configureGroupByResolver(OptionsResolver $resolver);
}
