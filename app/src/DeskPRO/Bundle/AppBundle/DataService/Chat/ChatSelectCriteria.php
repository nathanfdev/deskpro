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
use Application\DeskPRO\Entity\Person;

/**
 * Class ChatSelectCriteria
 */
class ChatSelectCriteria
{
    /**
     * @var array
     */
    protected $filters = [];

    /**
     * ChatCountCriteria constructor.
     *
     * @param array $filters
     */
    protected function __construct(array $filters)
    {
        $this->filters = $filters;
    }


    /**
     * @param array $params
     * @param OptionsResolver $resolver
     * @param Person $me
     * @return ChatSelectCriteria
     */
    public static function fromParameters(array $params, OptionsResolver $resolver, Person $me)
    {
        self::configureResolver($resolver, $me);
        $filters = $resolver->resolve($params);

        return new self($filters);
    }

    /**
     * @param QueryBuilder $qb
     */
    public function applyFilters(QueryBuilder $qb)
    {
        $alias = $qb->getRootAliases()[0];

        foreach ($this->filters as $field => $value) {
            switch ($field) {
                case 'date_created':
                    list($from, $to) = explode(':', $value);
                    $qb->andWhere($qb->expr()->gte("DATE($alias.date_created)", ':from'));
                    $qb->andWhere($qb->expr()->lte("DATE($alias.date_created)", ':to'));
                    $qb->setParameter('from', $from);
                    $qb->setParameter('to', $to);
                    break;

                case 'date_period':
                    $datePeriodCaseWhen = $this->getDatePeriodCaseWhenDql($alias);
                    $qb->andWhere("$datePeriodCaseWhen = :date_period");
                    $qb->setParameter('date_period', $value);
                    break;

                case 'agent':
                case 'department':
                    $qb->andWhere($qb->expr()->eq("$alias.$field", ":$field"));
                    $qb->setParameter($field, $value);
                    break;
            }
        }
    }

    /**
     * Get date_period CASE-WHEN DQL clause
     *
     * Handles the following groups:
     *
     * today
     * yesterday
     * this_week
     * this_month
     * last_month
     * this_year
     * ever
     *
     * @param string $alias
     * @return string
     */
    protected function getDatePeriodCaseWhenDql($alias)
    {
        $today = date('Y-m-d', strtotime('today'));
        $yesterday = date('Y-m-d', strtotime('yesterday'));
        $firstDayOfThisWeek = date('Y-m-d', strtotime('monday this week'));
        $firstDayOfThisMonth = date('Y-m-d', strtotime('first day of this month'));
        $firstDayOfLastMonth = date('Y-m-d', strtotime('first day of -1 month'));
        $firstDayOfThisYear = date('Y-01-01');

        $target = "DATE($alias.date_created)";

        $groupSelectDql = "(CASE
            WHEN $target  = '$today' THEN 'today'
            WHEN $target  = '$yesterday' THEN 'yesterday'
            WHEN $target >= '$firstDayOfThisWeek' THEN 'this_week'
            WHEN $target >= '$firstDayOfThisMonth' THEN 'this_month'
            WHEN $target >= '$firstDayOfLastMonth' THEN 'last_month'
            WHEN $target >= '$firstDayOfThisYear' THEN 'this_year'
            ELSE 'ever'
        END)";

        return $groupSelectDql;
    }

    /**
     * @param OptionsResolver $resolver
     * @param Person $me
     */
    protected static function configureResolver(OptionsResolver $resolver, Person $me)
    {
        $resolver->setDefined(['agent', 'department', 'date_created', 'date_period']);

        $resolver->setNormalizer('agent', function($options, $value) use ($me) {
            return $value === 'me' ? $me->getId() : $value;
        });
        $resolver->setAllowedValues('agent', function($value) {
            return is_int($value) || ctype_digit($value) || ($value === 'me');
        });
        $resolver->setAllowedValues('department', function($value) {
            return is_int($value) || ctype_digit($value);
        });
        $resolver->setAllowedValues('date_created', function($value) {
            return (bool) preg_match('/\d{4}\-\d{2}\-\d{2}\:\d{4}\-\d{2}\-\d{2}/', $value);
        });
        $resolver->setAllowedValues('date_period', [
            'today', 'yesterday', 'this_week', 'this_month', 'last_month', 'this_year', 'ever'
        ]);
    }
}
