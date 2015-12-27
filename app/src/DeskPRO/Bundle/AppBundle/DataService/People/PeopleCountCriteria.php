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

namespace DeskPRO\Bundle\AppBundle\DataService\People;

use DeskPRO\Bundle\AppBundle\Data\Criteria\Criteria;
use DeskPRO\Bundle\AppBundle\Data\Criteria\Groupable;
use DeskPRO\Bundle\AppBundle\Data\Criteria\GroupableCriteriaInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PeopleCountCriteria.
 */
class PeopleCountCriteria extends Criteria implements GroupableCriteriaInterface
{
    use Groupable;

    /**
     * {@inheritdoc}
     */
    public function getGroupByAllowedValues()
    {
        return ['user_group', 'agent_team'];
    }

    /**
     * @param QueryBuilder $qb
     */
    public function applyFilters(QueryBuilder $qb)
    {
        $alias = $qb->getRootAliases()[0];

        foreach ($this->filters as $field => $value) {
            switch ($field) {
                case 'is_agent':
                case 'is_deleted':
                    $qb->andWhere($qb->expr()->eq("$alias.$field", ":$field"));
                    $qb->setParameter($field, $value);
                    break;
            }
        }
    }

    /**
     * @param QueryBuilder $qb
     *
     * @throws \LogicException
     */
    public function applyGroupBy(QueryBuilder $qb)
    {
        $alias = $qb->getRootAliases()[0];
        if ($this->group_by === 'user_group') {
            $qb
                ->leftJoin("$alias.usergroups", 'groups')
                ->addSelect('groups.title as title')
                ->addSelect('groups.id as group_name')
                ->andWhere('groups.is_agent_group = false')
                ->andWhere('groups.is_enabled = true');
        } elseif ($this->group_by === 'agent_team') {
            $qb
                ->leftJoin("$alias.teams", 'teams')
                ->addSelect('teams.name as title')
                ->addSelect('teams.id as group_name');
        }
        $qb->groupBy('group_name');
    }

    /**
     * {@inheritdoc}
     */
    public static function configureResolver(OptionsResolver $resolver, array $data = [])
    {
        $resolver->setDefined(['is_agent', 'is_deleted', 'user_group']);
        $resolver->setAllowedValues('is_agent', ['0', '1']);
        $resolver->setAllowedValues('is_deleted', ['0', '1']);
        $resolver->setAllowedValues(
            'user_group',
            function ($value) {
                is_array($value) or $value = [$value];
                foreach ($value as $categoryId) {
                    if (!is_int($categoryId) && !ctype_digit($categoryId)) {
                        return false;
                    }
                }

                return true;
            }
        );
    }
}
