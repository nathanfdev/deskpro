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


use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Application\DeskPRO\Entity\Person;

/**
 * Class FeedbackCountCriteria
 *
 * This class uses the same filtering criteria as FeedbackSelectCriteria and additionally adds GROUP BY functionality
 */
class FeedbackCountCriteria extends FeedbackSelectCriteria
{
    /**
     * @var string
     */
    private $group_by;

    /**
     * ChatCountCriteria constructor.
     *
     * @param array $filters
     * @param string $group_by
     */
    protected function __construct(array $filters, $group_by)
    {
        parent::__construct($filters);
        $this->group_by = $group_by;
    }

    /**
     * @param array $params
     * @param OptionsResolver $resolver
     * @param Person $me
     * @return FeedbackCountCriteria
     * @throws AccessException
     * @throws UndefinedOptionsException
     */
    public static function fromParameters(array $params, OptionsResolver $resolver, Person $me)
    {
        self::configureResolver($resolver, $me);
        $params = $resolver->resolve($params);
        $group_by = null;
        if (array_key_exists('group_by', $params)) {
            $group_by = $params['group_by'];
            unset($params['group_by']);
        }
        $filters = $params;
        return new self($filters, $group_by);
    }

    /**
     * @return bool
     */
    public function isGrouped()
    {
        return (bool)$this->group_by;
    }

    /**
     * @return string
     */
    public function getGroupBy()
    {
        return $this->group_by;
    }

    /**
     * @param QueryBuilder $qb
     * @throws \LogicException
     */
    public function applyGroupBy(QueryBuilder $qb)
    {
        if (!$this->isGrouped()) {
            throw new \LogicException('Cannot group without group_by');
        }
        $alias = $qb->getRootAliases()[0];
        switch ($this->group_by) {
            case 'status_category':
                $qb->addSelect("DATE($alias.date_created) as group_name");
                break;
            case 'hidden_status':
                $qb->addSelect($this->getDatePeriodCaseWhenDql($alias) . ' as group_name');
                // select hidden group_order to use in ORDER BY
                $qb->addSelect(
                    "FIELD(" . $this->getDatePeriodCaseWhenDql($alias) . ",
                        'today', 'yesterday', 'this_month', 'last_month', 'this_year', 'ever'
                    ) as HIDDEN group_order");
                $qb->orderBy('group_order');
                break;
            case 'category':
                $qb->addSelect('g.id as group_name');
                $qb->leftJoin("{$alias}.{$this->group_by}", 'g');
                break;
        }
        $qb->groupBy('group_name');
    }

    /**
     * @param OptionsResolver $resolver
     * @param Person $me
     * @throws AccessException
     * @throws UndefinedOptionsException
     */
    protected static function configureResolver(OptionsResolver $resolver, Person $me)
    {
        parent::configureResolver($resolver, $me);
        $resolver->setDefined(array_merge($resolver->getDefinedOptions(), ['group_by']));
        $resolver->setAllowedValues('group_by', ['status_category', 'hidden_status', 'category']);
    }
}
