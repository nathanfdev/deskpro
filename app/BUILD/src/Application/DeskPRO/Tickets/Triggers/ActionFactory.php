<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\Triggers;

use Application\DeskPRO\Tickets\Actions\AbstractAction;
use Application\DeskPRO\Tickets\Actions\AppActionInterface;
use Orb\Util\Strings;

class ActionFactory
{
    public function createFromArray(array $action_info)
    {
        if (isset($action_info['type_class'])) {
            $construct_options = [
                'type' => $action_info['type'],
            ];

            return $this->create("@{$action_info['type_class']}", $action_info['options'], $construct_options);
        } else {
            return $this->create($action_info['type'], $action_info['options']);
        }
    }

    public function create($type, array $options, array $construct_options = null)
    {
        if ($type[0] == '@') {
            $class_name = substr($type, 1);
        } else {
            if (preg_match('#^Set(User|Ticket|Org)(Contextual)?Field(\d+)$#', $type, $m)) {
                $class_type          = 'Set'.$m[1].$m[2].'Field';
                $options['field_id'] = $m[3];
            } else {
                $class_type = $type;
            }
            $class_name = "Application\\DeskPRO\\Tickets\\Actions\\$class_type";
        }

        if (!class_exists($class_name)) {
            throw new \InvalidArgumentException("Unknown action $type (could not locate class: $class_name)");
        }

        $term = new $class_name($options);

        // Set app ID on app actions
        if ($term instanceof AppActionInterface && $term instanceof AbstractAction && $construct_options) {
            $id = Strings::extractRegexMatch('#(\d+)$#', $construct_options['type']);
            if ($id) {
                $term->getMetaData()->set('app_id', (int) $id);
            }
        }

        return $term;
    }
}
