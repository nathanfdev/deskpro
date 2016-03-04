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

namespace DeskPRO\Bundle\AppBundle\DataService\Chat;

use DeskPRO\Bundle\AppBundle\Data\Criteria\Criteria;
use DeskPRO\Bundle\AppBundle\Data\Criteria\Sortable;
use DeskPRO\Bundle\AppBundle\Data\Criteria\SortableCriteriaInterface;
use DeskPRO\Bundle\AppBundle\Data\DatePeriods;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ChatSelectCriteria.
 */
class ChatSelectCriteria extends Criteria implements SortableCriteriaInterface
{
    use Sortable;

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
                    $datePeriodCaseWhen = DatePeriods::getDatePeriodCaseWhenDql("$alias.date_created");
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
     * {@inheritdoc}
     */
    public function getSortAllowedValues()
    {
        return ['agent', 'department', 'date_created'];
    }

    /**
     * @param OptionsResolver $resolver
     * @param array           $data
     */
    public static function configureResolver(OptionsResolver $resolver, array $data = [])
    {
        /** @var \Application\DeskPRO\Entity\Person $me */
        list($me) = $data;

        $resolver->setDefined(['agent', 'department', 'date_created', 'date_period']);

        $resolver->setNormalizer('agent', function ($options, $value) use ($me) {
            return $value === 'me' ? $me->getId() : $value;
        });
        $resolver->setAllowedValues('agent', function ($value) {
            return is_int($value) || ctype_digit($value) || ($value === 'me');
        });
        $resolver->setAllowedValues('department', function ($value) {
            return is_int($value) || ctype_digit($value);
        });
        $resolver->setAllowedValues('date_created', function ($value) {
            return (bool) preg_match('/\d{4}\-\d{2}\-\d{2}\:\d{4}\-\d{2}\-\d{2}/', $value);
        });
        $resolver->setAllowedValues('date_period', DatePeriods::$names);
    }
}
