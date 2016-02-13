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

use DeskPRO\Bundle\AppBundle\Data\Criteria\Criteria;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ArticlePendingCreateCriteria.
 */
class ArticlePendingCreateCriteria extends Criteria
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
                case 'assigned_person':
                    $qb->andWhere("$alias.assigned_person = :id");
                    $qb->setParameter('id', $value);
                    break;
                case 'sort':
                    $sort = "$alias.$value";
                    break;
                case 'order':
                    $order = "$value";
                    break;
            }
        }
        $qb->orderBy($sort, $order);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public static function configureResolver(OptionsResolver $resolver, array $data = [])
    {
        $resolver->setDefined(
            [
                'assigned_person',
                'order',
                'sort',
            ]
        );

        $resolver->setAllowedValues(
            'assigned_person',
            function ($value) {
                return is_int($value) || ctype_digit($value) || ($value === 'me');
            }
        );

        $resolver->setAllowedValues('order', ['asc', 'desc']);
    }
}
