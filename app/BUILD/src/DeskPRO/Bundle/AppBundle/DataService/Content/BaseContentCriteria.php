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
namespace DeskPRO\Bundle\AppBundle\DataService\Content;

use Application\DeskPRO\Entity\ContentAbstract as Content;
use DeskPRO\Bundle\AppBundle\Data\Criteria\Criteria;
use DeskPRO\Bundle\AppBundle\Data\Criteria\Groupable;
use DeskPRO\Bundle\AppBundle\Data\Criteria\GroupableCriteriaInterface;
use DeskPRO\Bundle\AppBundle\Data\Criteria\Sortable;
use DeskPRO\Bundle\AppBundle\Data\Criteria\SortableCriteriaInterface;
use DeskPRO\Bundle\AppBundle\Data\DatePeriods;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BaseContentCriteria.
 */
class BaseContentCriteria extends Criteria implements GroupableCriteriaInterface, SortableCriteriaInterface
{
    use Groupable;
    use Sortable;

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
                    if (is_array($value)) {
                        $qb->andWhere("$alias.$field IN (:$field)");
                    } else {
                        $qb->andWhere("$alias.$field = :$field");
                    }
                    $qb->setParameter($field, $value);
                    break;

                case 'period_created':
                    $datePeriodCaseWhen = DatePeriods::getDatePeriodCaseWhenDql("$alias.date_created");
                    $qb
                        ->andWhere("$datePeriodCaseWhen = :period_created")
                        ->setParameter('period_created', $value);
                    break;

                case 'period_updated':
                    $datePeriodCaseWhen = DatePeriods::getDatePeriodCaseWhenDql("$alias.date_updated");
                    $qb
                        ->andWhere("$datePeriodCaseWhen = :period_updated")
                        ->setParameter('period_updated', $value);
                    break;

                case 'period_published':
                    $datePeriodCaseWhen = DatePeriods::getDatePeriodCaseWhenDql("$alias.date_published");
                    $qb
                        ->andWhere("$datePeriodCaseWhen = :period_published")
                        ->setParameter('period_published', $value);
                    break;

                case 'period_last_comment':
                    $datePeriodCaseWhen = DatePeriods::getDatePeriodCaseWhenDql("$alias.date_last_comment");
                    $qb
                        ->andWhere("$datePeriodCaseWhen = :period_last_comment")
                        ->setParameter('period_last_comment', $value);
                    break;

                case 'author':
                    $qb->andWhere("$alias.person = :person");
                    $qb->setParameter('person', $value);
                    break;
            }
        }
    }

    /**
     * @return array
     */
    public function getSortAllowedValues()
    {
        return ['date_created', 'date_updated', 'person'];
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupByAllowedValues()
    {
        return ['author', 'category', 'period_created', 'period_updated'];
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
                $qb->addSelect('p.name as title');
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
     * @return bool
     */
    public function isGroupedByCategory()
    {
        return $this->group_by === 'category';
    }

    /**
     * @param OptionsResolver $resolver
     * @param array           $data
     */
    public static function configureResolver(OptionsResolver $resolver, array $data = [])
    {
        /** @var \Application\DeskPRO\Entity\Person $me */
        list($me) = $data;

        $resolver->setDefined(
            [
                'status',
                'hidden_status',
                'author',
                'category',
                'period_created',
                'period_updated',
                'period_published',
                'period_last_comment',
                'order',
                'sort',
            ]
        );

        // filters validation
        $resolver->setAllowedValues(
            'category',
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

        $resolver->setNormalizer(
            'author',
            function ($options, $value) use ($me) {
                return $value === 'me' ? $me->getId() : $value;
            }
        );
        $resolver->setAllowedValues(
            'author',
            function ($value) {
                return is_int($value) || ctype_digit($value) || ($value === 'me');
            }
        );

        $resolver->setAllowedValues(
            'status',
            function ($value) {
                $allowed = Content::getAllStatuses();
                is_array($value) or $value = [$value];
                foreach ($value as $status) {
                    if (!in_array($status, $allowed)) {
                        return false;
                    }
                }

                return true;
            }
        );
        $resolver->setAllowedValues(
            'hidden_status',
            function ($value) {
                $allowed = Content::getAllHiddenStatuses();
                is_array($value) or $value = [$value];
                foreach ($value as $status) {
                    if (!in_array($status, $allowed)) {
                        return false;
                    }
                }

                return true;
            }
        );
        $resolver->setAllowedValues('period_created', DatePeriods::$names);
        $resolver->setAllowedValues('period_updated', DatePeriods::$names);
        $resolver->setAllowedValues('period_published', DatePeriods::$names);
        $resolver->setAllowedValues('period_last_comment', DatePeriods::$names);
        $resolver->setAllowedValues('order', ['asc', 'desc']);
    }
}
