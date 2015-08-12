<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/
/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\DataService\Feedback;

use Application\DeskPRO\Entity\Feedback;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

class FeedbackSelectCriteria
{
    /**
     * @var array
     */
    protected $filters = [];

    /**
     * FeedbackCountCriteria constructor.
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
     * @return FeedbackSelectCriteria
     * @throws AccessException
     * @throws UndefinedOptionsException
     */
    public static function fromParameters(array $params, OptionsResolver $resolver)
    {
        self::configureResolver($resolver);
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
                case 'awaiting_validation':
                    $qb->andWhere($qb->expr()->eq("$alias.hidden_status", 'validating'));
                    break;
                case 'status':
                    $qb->andWhere("$alias.status = :status");
                    $qb->setParameter('status', $value);
                    break;
            }
        }
    }

    /**
     * @param OptionsResolver $resolver
     * @throws AccessException
     * @throws UndefinedOptionsException
     */
    protected static function configureResolver(OptionsResolver $resolver)
    {
        $resolver->setDefined(['awaiting_validation', 'status']);
        $resolver->setAllowedValues('awaiting_validation', 1);
        $resolver->setAllowedValues(
            'status',
            ['new', Feedback::STATUS_ACTIVE, Feedback::STATUS_CLOSED, Feedback::STATUS_HIDDEN]
        );
    }
}
