<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Filters;

class FilterTermFactory
{
    public function createFromArray(array $term_info)
    {
        if (empty($term_info['type'])) {
            throw new \InvalidArgumentException('Missing type');
        }

        if (empty($term_info['op'])) {
            $term_info['op'] = 'is';
        }

        if (empty($term_info['options'])) {
            $term_info['options'] = [];
        }

        return $this->create($term_info['type'], $term_info['op'], $term_info['options']);
    }

    public function create($type, $op, array $options)
    {
        if (preg_match('#^Filter(User|Ticket|Org)Field(\d+)$#', $type, $m)) {
            $class_type          = 'Filter'.$m[1].'Field';
            $options['field_id'] = $m[2];
        } else {
            $class_type = $type;
        }

        $class_name = "Application\\DeskPRO\\Tickets\\Filters\\Terms\\$class_type";
        if (!class_exists($class_name)) {
            throw new \InvalidArgumentException("Unknown term $type (could not locate class: $class_name)");
        }

        $term = new $class_name($op, $options);

        return $term;
    }
}
