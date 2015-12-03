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

namespace DeskPRO\Bundle\AppBundle\DataService\Content\Comment;

use Application\DeskPRO\Entity\CommentAbstract as Comment;
use DeskPRO\Bundle\AppBundle\Data\Criteria\Criteria;
use DeskPRO\Bundle\AppBundle\Data\DatePeriods;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CommentsSelectCriteria.
 */
class CommentsSelectCriteria extends Criteria
{
    /**
     * {@inheritdoc}
     */
    public function applyFilters(QueryBuilder $qb)
    {
        $alias = $qb->getRootAliases()[0];

        foreach ($this->filters as $field => $value) {
            switch ($field) {
                case 'article':
                case 'news':
                case 'download':
                case 'status':
                case 'is_reviewed':
                    $qb->andWhere("$alias.$field = :$field");
                    $qb->setParameter($field, $value);
                    break;

                case 'period_created':
                    $datePeriodCaseWhen = DatePeriods::getDatePeriodCaseWhenDql("$alias.date_created");
                    $qb->andWhere("$datePeriodCaseWhen = :period_created");
                    $qb->setParameter('period_created', $value);
                    break;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function configureResolver(OptionsResolver $resolver, array $data = [])
    {
        $resolver->setDefined(['article', 'news', 'download', 'status', 'period_created', 'is_reviewed']);

        $validateInt = function ($value) {
            return is_int($value) || ctype_digit($value);
        };
        $resolver->setAllowedValues('article', $validateInt);
        $resolver->setAllowedValues('news', $validateInt);
        $resolver->setAllowedValues('download', $validateInt);
        $resolver->setAllowedValues('status', [
            Comment::STATUS_VISIBLE,
            Comment::STATUS_DELETED,
            Comment::STATUS_AGENT,
        ]);
        $resolver->setAllowedValues('period_created', DatePeriods::$names);
        $resolver->setAllowedValues('is_reviewed', ['0', '1']);
    }
}
