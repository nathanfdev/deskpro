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

namespace DeskPRO\Bundle\AppBundle\DataService\Content\ContentCount;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\QueryBuilder;
use DeskPRO\Bundle\AppBundle\Data\Criteria\GroupedCriteria;
use DeskPRO\Bundle\AppBundle\Data\DatePeriods;
use Application\DeskPRO\Entity\ContentAbstract as Content;

/**
 * Class BaseContentCountCriteria
 */
abstract class BaseContentCountCriteria extends GroupedCriteria
{
    /**
     * @param QueryBuilder $qb
     */
    public function applyFilters(QueryBuilder $qb)
    {
        $alias = $qb->getRootAliases()[0];

        foreach ($this->filters as $field => $value) {
            switch ($field) {
                case 'status':
                case 'hidden_status':
                    $qb->andWhere("$alias.$field = :$field");
                    $qb->setParameter($field, $value);
                    break;

                case 'period_created':
                    $datePeriodCaseWhen = DatePeriods::getDatePeriodCaseWhenDql("$alias.date_created");
                    $qb->andWhere("$datePeriodCaseWhen = :period_created");
                    $qb->setParameter('period_created', $value);
                    break;

                case 'author':
                    $qb->andWhere("$alias.person = :person");
                    $qb->setParameter('person', $value);
                    break;
            }
        }
    }

    /**
     * @param QueryBuilder $qb
     */
    public function applyGroupBy(QueryBuilder $qb)
    {
        $this->ensureGroupBy();

        $alias = $qb->getRootAliases()[0];
        switch ($this->group_by) {
            case 'author':
                $qb->addSelect('p.id as group_name');
                $qb->leftJoin("$alias.person", 'p');
                break;

            case 'period_created':
                $datePeriodCaseWhen = DatePeriods::getDatePeriodCaseWhenDql("$alias.date_created");
                $qb->addSelect("$datePeriodCaseWhen as group_name");
                break;

            case 'period_updated':
                $datePeriodCaseWhen =
                    DatePeriods::getDatePeriodCaseWhenDql("COALESCE($alias.date_updated, $alias.date_created)");
                $qb->addSelect("$datePeriodCaseWhen as group_name");
                break;
        }

        $qb->groupBy('group_name');
    }

    /**
     * @param OptionsResolver $resolver
     */
    public static function configureResolver(OptionsResolver $resolver, array $data = [])
    {
        /** @var \Application\DeskPRO\Entity\Person $me */
        list($me) = $data;

        $resolver->setDefined(['group_by', 'status', 'hidden_status', 'author', 'category', 'period_created']);

        // group_by validation
        $resolver->setAllowedValues('group_by', ['author', 'category', 'period_created', 'period_updated']);

        // filters validation
        $validateInt = function($value) {
            return is_int($value) || ctype_digit($value);
        };
        $resolver->setAllowedValues('category', $validateInt);

        $resolver->setNormalizer('author', function($options, $value) use ($me) {
            return $value === 'me' ? $me->getId() : $value;
        });
        $resolver->setAllowedValues('author', function($value) {
            return is_int($value) || ctype_digit($value) || ($value === 'me');
        });

        $resolver->setAllowedValues('status', Content::getAllStatuses());
        $resolver->setAllowedValues('hidden_status', Content::getAllHiddenStatuses());
        $resolver->setAllowedValues('period_created', DatePeriods::$names);
    }

    /**
     * @return bool
     */
    public function isGroupedByCategory()
    {
        return $this->group_by === 'category';
    }
}
