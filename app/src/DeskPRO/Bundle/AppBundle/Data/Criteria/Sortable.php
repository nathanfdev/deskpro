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
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\Data\Criteria;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\QueryBuilder;

/**
 * Class Sortable
 */
trait Sortable
{
    /**
     * @var string
     */
    private $sort;

    /**
     * @var string
     */
    private $order;

    /**
     * @inheritDoc
     */
    abstract public function getSortAllowedValues();

    /**
     * @inheritDoc
     */
    public function getOrderAllowedValues()
    {
        return ['asc', 'desc'];
    }

    /**
     * @inheritDoc
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @inheritDoc
     */
    public function setSort($value)
    {
        $this->sort = $value;
    }

    /**
     * @inheritDoc
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @inheritDoc
     */
    public function setOrder($value)
    {
        $this->order = $value;
    }

    /**
     * @inheritDoc
     */
    public function hasSorting()
    {
        return (bool) $this->order && (bool) $this->sort;
    }

    /**
     * @inheritDoc
     */
    public static function extractSorting(array &$params)
    {
        $sort = null;
        if (array_key_exists('sort', $params)) {
            $sort = $params['sort'];
            unset($params['sort']);
        }

        $order = 'desc';
        if (array_key_exists('order', $params)) {
            $order = $params['order'];
            unset($params['order']);
        }

        return [$sort, $order];
    }

    /**
     * @param QueryBuilder $qb
     */
    public function applySorting(QueryBuilder $qb)
    {
        if ($this->hasSorting()) {
            $alias = $qb->getRootAliases()[0];
            $qb->orderBy("$alias.$this->sort", $this->order);
        }
    }

    /**
     * @inheritDoc
     */
    public static function configureSortingResolver(OptionsResolver $resolver)
    {
        $resolver->setDefined(array_merge($resolver->getDefinedOptions(), ['sort', 'order']));
        $resolver->setAllowedValues('sort', (new self())->getSortAllowedValues());
        $resolver->setAllowedValues('order', (new self())->getOrderAllowedValues());
    }
}