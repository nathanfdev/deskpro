<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\Triggers;

class TermFactory
{
    public function createFromArray(array $term_info, $namespace = 'Application\\DeskPRO\\Tickets\\Triggers\\Terms')
    {
        return $this->create($term_info['type'], $term_info['op'], $term_info['options'], $namespace);
    }

    public function create($type, $op, array $options, $namespace = 'Application\\DeskPRO\\Tickets\\Triggers\\Terms')
    {
        if (preg_match('#^Check(User|Ticket|Org)(Contextual)?Field(\d+)$#', $type, $m)) {
            $class_type          = 'Check'.$m[1].$m[2].'Field';
            $options['field_id'] = $m[3];
        } else {
            $class_type = $type;
        }

        $class_name = "$namespace\\$class_type";
        if (!class_exists($class_name)) {
            throw new \InvalidArgumentException("Unknown term $type (could not locate class: $class_name)");
        }

        $term = new $class_name($op, $options);

        return $term;
    }
}
