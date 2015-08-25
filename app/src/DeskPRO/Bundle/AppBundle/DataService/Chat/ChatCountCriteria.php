<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataService\Chat;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\QueryBuilder;
use DeskPRO\Bundle\AppBundle\Data\DatePeriods;

/**
 * Class ChatCountCriteria
 */
class ChatCountCriteria extends ChatSelectCriteria
{
    /**
     * @param QueryBuilder $qb
     */
    public function applyGroupBy(QueryBuilder $qb)
    {
        $this->ensureGroupBy();

        $alias = $qb->getRootAliases()[0];
        switch ($this->group_by) {
            case 'date_created':
                $qb->addSelect("DATE($alias.date_created) as group_name");
                break;

            case 'date_period':
                $datePeriodsDql = DatePeriods::getDatePeriodCaseWhenDql("$alias.date_created");
                $qb->addSelect("$datePeriodsDql as group_name");

                // select hidden group_order to use in ORDER BY
                $qb->addSelect(
                    "FIELD($datePeriodsDql, 'today', 'yesterday', 'this_month', 'last_month', 'this_year', 'ever')
                     as HIDDEN group_order");
                $qb->orderBy('group_order');

                break;

            case 'agent':
            case 'department':
                $qb->addSelect('g.id as group_name');
                $qb->leftJoin("{$alias}.{$this->group_by}", 'g');
                break;
        }

        $qb->groupBy('group_name');
    }

    /**
     * @param OptionsResolver $resolver
     * @param array $data
     */
    public static function configureResolver(OptionsResolver $resolver, array $data = [])
    {
        parent::configureResolver($resolver, $data);

        $resolver->setDefined(array_merge($resolver->getDefinedOptions(), ['group_by']));
        $resolver->setAllowedValues('group_by', ['agent', 'department', 'date_created', 'date_period']);
    }
}
