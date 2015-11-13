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
namespace DeskPRO\Bundle\AppBundle\DataService\Tasks;

use DeskPRO\Bundle\AppBundle\Data\Criteria\Criteria;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TasksSelectCriteria.
 */
class TasksSelectCriteria extends Criteria
{
    /**
     * {@inheritdoc}
     */
    public function applyFilters(QueryBuilder $qb)
    {
        foreach ($this->filters as $field => $value) {
            switch ($field) {
                case 'ids':
                    $qb
                        ->andWhere("t.id IN (:$field)")
                        ->setParameter($field, $value)
                    ;

                    break;
                case 'label':
                    $qb
                        ->andWhere("l.label IN (:$field)")
                        ->setParameter($field, $value)
                    ;

                    break;
                case 'project':
                    $qb
                        ->andWhere("t.project IN (:$field)")
                        ->setParameter($field, $value)
                    ;

                    break;
                case 'creator':
                    $qb
                        ->andWhere("t.creator IN (:$field)")
                        ->setParameter($field, $value)
                    ;

                    break;
                case 'no_assignments':
                    $qb
                        ->andWhere('ta.person IS NULL')
                        ->andWhere('ta.team IS NULL')
                        ->andWhere('ta.department IS NULL')
                    ;

                    break;
                case 'assigned_agent':
                    $qb
                        ->andWhere("ta.person IN (:$field)")
                        ->setParameter($field, $value)
                    ;

                    break;
                case 'not_assigned_agent':
                    $qb
                        ->andWhere("ta.person NOT IN (:$field)")
                        ->setParameter($field, $value)
                    ;

                    break;
                case 'assigned_team':
                    $qb
                        ->andWhere("ta.team NOT IN (:$field)")
                        ->setParameter($field, $value)
                    ;

                    break;
                case 'not_assigned_team':
                    $qb
                        ->andWhere("ta.team NOT IN (:$field)")
                        ->setParameter($field, $value)
                    ;

                    break;
                case 'assigned_department':
                    $qb
                        ->andWhere("ta.department IN (:$field)")
                        ->setParameter($field, $value)
                    ;

                    break;
                case 'not_assigned_department':
                    $qb
                        ->andWhere('ta.department IS NULL')
                        ->setParameter($field, $value)
                    ;

                    break;
                case 'created_from':
                    $qb
                        ->andWhere("t.date_created >= DATE(:$field)")
                        ->setParameter($field, $value)
                    ;

                    break;
                case 'created_to':
                    $qb
                        ->andWhere("t.date_created <= DATE(:$field)")
                        ->setParameter($field, $value)
                    ;

                    break;
                case 'due_from':
                    $qb
                        ->andWhere("t.date_due >= DATE(:$field)")
                        ->setParameter($field, $value)
                    ;

                    break;
                case 'due_to':
                    $qb
                        ->andWhere("t.date_due <= DATE(:$field)")
                        ->setParameter($field, $value)
                    ;

                    break;
                case 'done_from':
                    $qb
                        ->andWhere("t.date_done >= DATE(:$field)")
                        ->setParameter($field, $value)
                    ;

                    break;
                case 'done_to':
                    $qb
                        ->andWhere("t.date_done <= DATE(:$field)")
                        ->setParameter($field, $value)
                    ;

                    break;
                case 'done':
                    $qb
                        ->andWhere("t.is_done = :$field")
                        ->setParameter($field, (int) $value)
                    ;

                    break;
                case 'sort':
                    $order = isset($this->filters['order']) ? $this->filters['order'] : 'asc';

                    switch ($value) {
                        case 'project':
                            $qb->orderBy('p.title', $order);
                            break;
                        case 'date_due':
                        case 'date_done':
                        case 'date_created':
                            $qb->orderBy("t.$value", $order);
                            break;
                        case 'assignee':
                            $qb
                                ->leftJoin('ta.person', 'person')
                                ->leftJoin('ta.team', 'team')
                                ->leftJoin('ta.department', 'department')
                                ->orderBy('person.name', $order)
                                ->orderBy('team.name', $order)
                                ->orderBy('department.title', $order)
                            ;
                    }

                    break;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function configureResolver(OptionsResolver $resolver, array $data = [])
    {
        /** @var \Application\DeskPRO\Entity\Person $me */
        list($me) = $data;

        $person_validator = function ($value) {
            if (is_null($value)) {
                return true;
            }

            $value = (array) $value;
            foreach ($value as $id) {
                if (!preg_match('/^(me|\d+)$/', $id)) {
                    return false;
                }
            }

            return true;
        };

        $person_normalizer = function (Options $options, $value) use ($me) {
            $value = (array) $value;
            foreach ($value as &$person) {
                if ($person === 'me') {
                    $person = $me->getId();
                }
            }

            return $value;
        };

        $resolver
            ->setDefined([
                'ids',
                'no_assignments',
                'assigned_agent',
                'not_assigned_agent',
                'assigned_team',
                'not_assigned_team',
                'assigned_department',
                'not_assigned_department',
                'creator',
                'project',
                'sort',
                'order',
                'label',
                'created_from',
                'created_to',
                'due_from',
                'due_to',
                'done_from',
                'done_to',
                'done',
            ])
            ->setAllowedValues('assigned_agent', $person_validator)
            ->setNormalizer('assigned_agent', $person_normalizer)
            ->setAllowedValues('not_assigned_agent', $person_validator)
            ->setNormalizer('not_assigned_agent', $person_normalizer)
            ->setAllowedValues('assigned_team', $person_validator)
            ->setNormalizer('assigned_team', $person_normalizer)
            ->setAllowedValues('not_assigned_team', $person_validator)
            ->setNormalizer('not_assigned_team', $person_normalizer)
            ->setAllowedValues('assigned_department', $person_validator)
            ->setNormalizer('assigned_department', $person_normalizer)
            ->setAllowedValues('not_assigned_department', $person_validator)
            ->setNormalizer('not_assigned_department', $person_normalizer)
            ->setAllowedValues('creator', $person_validator)
            ->setNormalizer('creator', $person_normalizer)
            ->setAllowedValues('sort', [
                'project',
                'date_due',
                'date_done',
                'date_created',
                'assignee',
            ])
            ->setAllowedValues('order', [
                'asc',
                'desc',
            ])
        ;
    }
}
