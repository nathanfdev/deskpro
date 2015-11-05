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
namespace DeskPRO\Bundle\AppBundle\DataService\Feedback;

use DeskPRO\Bundle\AppBundle\Data\Criteria\Criteria;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeedbackSelectCriteria extends Criteria
{
    /**
     * @param QueryBuilder $qb
     */
    public function applyFilters(QueryBuilder $qb)
    {
        $alias = $qb->getRootAliases()[0];
        $sort  = "$alias.date_created";
        $order = 'asc';
        foreach ($this->filters as $field => $value) {
            switch ($field) {
                case 'ids':
                    $qb
                        ->andWhere("$alias.id IN (:ids)")
                        ->setParameter('ids', explode(',', $value));
                    break;
                case 'awaiting_validation':
                    $qb
                        ->andWhere("$alias.hidden_status = :validating")
                        ->setParameter('validating', 'validating');
                    break;
                case 'category':
                    if (is_array($value)) {
                        $qb->andWhere('category.title IN (:category_title)');
                    } else {
                        $qb->andWhere('category.title = :category_title');
                    }
                    $qb->setParameter('category_title', $value);
                    break;
                case 'status_category':
                    if (is_array($value)) {
                        $qb->andWhere('statusCategory.title IN (:title)');
                    } else {
                        $qb->andWhere('statusCategory.title = :title');
                    }
                    $qb->setParameter('title', $value);
                    break;
                case 'label':
                    $qb
                        ->andWhere('labels.label = :label')
                        ->setParameter('label', $value);
                    break;
                case 'no_labels':
                    $qb
                        ->andWhere('labels.label IS NULL');
                    break;
                case 'custom_category':
                    if (is_array($value)) {
                        $qb->andWhere('customCat.input IN (:input)');
                    } else {
                        $qb->andWhere('customCat.input = :input');
                    }
                    $qb->setParameter('input', $value);
                    break;
                case 'status':
                    if (is_array($value)) {
                        $qb->andWhere("$alias.status IN (:status)");
                    } else {
                        $qb->andWhere("$alias.status = :status");
                    }
                    $qb->setParameter('status', $value);
                    break;
                case 'hidden_status':
                    $qb
                        ->andWhere("$alias.hidden_status = :status")
                        ->setParameter('status', $value);
                    break;
                case 'created_from':
                    $qb
                        ->andWhere("$alias.date_created >= DATE(:from_date)")
                        ->setParameter('from_date', $value);
                    break;
                case 'created_to':
                    $qb
                        ->andWhere("$alias.date_created <= DATE(:to_date)")
                        ->setParameter('to_date', $value);
                    break;
                case 'sort':
                    $sort = "$alias.$value";
                    break;
                case 'order':
                    $order = "$value";
                    break;
            }
            $qb->orderBy($sort, $order);
        }
    }

    /**
     * @param OptionsResolver $resolver
     * @param array           $data
     *
     * @throws AccessException
     * @throws UndefinedOptionsException
     */
    public static function configureResolver(OptionsResolver $resolver, array $data = [])
    {
        $resolver->setDefined(
            [
                'awaiting_validation',
                'status',
                'hidden_status',
                'status_category',
                'category',
                'custom_category',
                'label',
                'no_labels',
                'sort',
                'order',
                'page',
                'count',
                'ids',
                'created_from',
                'created_to',
            ]
        );
        $resolver->setAllowedValues('awaiting_validation', '1');
        $resolver->setAllowedValues('no_labels', '1');
        $resolver->setAllowedValues(
            'sort',
            ['date_created', 'total_rating', 'num_ratings', 'id', 'title', 'status', 'category', 'author_name']
        );
        $resolver->setAllowedValues('order', ['asc', 'desc']);
    }
}
