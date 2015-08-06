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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ChatDataServiceCriteria
 */
class ChatDataServiceCriteria
{
    /**
     * @var array
     */
    private $filters = [];

    /**
     * @var string
     */
    private $group_by;

    /**
     * ChatDataServiceCriteria constructor.
     *
     * @param array $filters
     * @param string $group_by
     */
    private function __construct(array $filters, $group_by)
    {
        $this->filters = $filters;
        $this->group_by = $group_by;
    }


    /**
     * @param Request $request
     * @return ChatDataServiceCriteria
     */
    public static function fromRequest(Request $request, OptionsResolver $resolver)
    {
        $params = self::resolveParams($request, $resolver);

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
        return (bool) $this->group_by;
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
     */
    public function applyGroupBy(QueryBuilder $qb)
    {
        if (!$field = $this->group_by) {
            return;
        }

        $alias = $qb->getRootAliases()[0];

        switch ($field) {
            case 'date_created':
                $qb->addSelect("SUBSTRING($alias.date_created, 1, 10) as group_name");
                $qb->leftJoin("$alias.agent", 'a');
                break;

            case 'agent':
            case 'department':
                $qb->addSelect('g.id as group_name');
                $qb->leftJoin("$alias.$field", 'g');
                break;
        }

        $qb->groupBy('group_name');
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
                    $qb->andWhere($qb->expr()->gte("SUBSTRING($alias.date_created, 1, 10)", ':from'));
                    $qb->andWhere($qb->expr()->lte("SUBSTRING($alias.date_created, 1, 10)", ':to'));
                    $qb->setParameters(compact('from', 'to'));
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
     * @param Request $request
     * @param OptionsResolver $resolver
     * @return array
     */
    private static function resolveParams(Request $request, OptionsResolver $resolver)
    {
        $resolver->setDefined(['agent', 'department', 'date_created', 'group_by']);

        $resolver->setAllowedValues('agent', function($value) {
            return ctype_digit($value);
        });
        $resolver->setAllowedValues('department', function($value) {
            return ctype_digit($value);
        });
        $resolver->setAllowedValues('date_created', function($value) {
            return (bool) preg_match('/\d{4}\-\d{2}\-\d{2}\:\d{4}\-\d{2}\-\d{2}/', $value);
        });
        $resolver->setAllowedValues('group_by', ['agent', 'department', 'date_created']);

        $params = $resolver->resolve($request->query->all());

        return $params;
    }
}
