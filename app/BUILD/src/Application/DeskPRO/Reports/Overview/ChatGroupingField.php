<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Reports\Overview;

use Application\DeskPRO\App;

class ChatGroupingField extends GroupingField
{
    const DEPARTMENT   = 'department';
    const USER_FIELD   = 'user_field';
    const AGENT        = 'agent';
    const ORGANIZATION = 'organization';
    const USER         = 'user';
    const USERGROUP    = 'usergroup';

    public function getFieldInfo()
    {
        switch ($this->field) {
            case self::DEPARTMENT:
                return ['select'        => 'COALESCE(chat_conversations.department_id, 0) AS group_field',
                             'group_by' => 'group_field',
                             'join'     => '',
                             'where'    => '',
                ];
                break;

            case self::AGENT:
                return ['select'        => 'COALESCE(chat_conversations.agent_id, 0) AS group_field',
                             'group_by' => 'group_field',
                             'join'     => '',
                             'where'    => '',
                ];
                break;

            case self::ORGANIZATION:
                return [
                    'select'   => 'COALESCE(organizations.id, 0) AS org_id',
                    'group_by' => 'org_id',
                    'join'     => 'LEFT JOIN people ON (chat_conversations.person_id = people.id) LEFT JOIN organizations ON (organizations.id = people.organization_id)',
                    'where'    => '',
                ];
                break;

            case self::USERGROUP:
                return [
                    'select'   => 'COALESCE(person2usergroups.usergroup_id, 0) AS usergroup_id',
                    'group_by' => 'usergroup_id',
                    'join'     => 'LEFT JOIN person2usergroups ON (person2usergroups.person_id = chat_conversations.person_id)',
                    'where'    => '',
                ];
                break;

            case self::USER:
                return ['select'        => 'COALESCE(chat_conversations.person_id, 0) AS person_id',
                             'group_by' => 'person_id',
                             'join'     => '',
                             'where'    => '',
                ];
                break;

            case self::USER_FIELD:
                $field_def = App::getSystemService('person_fields_manager')->getFieldFromId($this->field_id);

                if ($field_def->isChoiceType()) {
                    $children = App::getSystemService('person_fields_manager')->getFieldChildren($field_def);
                    if (!$children) {
                        return [
                            'select'   => '0 as group_field',
                            'group_by' => 'group_field',
                            'join'     => '',
                            'where'    => '',
                        ];
                    }

                    $ids = implode(',', array_keys($children));

                    return [
                        'select'   => 'COALESCE(custom_data_person.id, 0) AS group_field',
                        'group_by' => 'group_field',
                        'join'     => 'LEFT JOIN custom_data_person ON (custom_data_person.person_id = chat_conversations.person_id AND custom_data_person.field_id IN('.$ids.'))',
                        'where'    => '',
                    ];
                } else {
                    return [
                        'select'   => 'COALESCE(custom_data_person.input, 0) AS group_field',
                        'group_by' => 'group_field',
                        'join'     => 'LEFT JOIN custom_data_person ON (custom_data_person.person_id = chat_conversations.person_id AND custom_data_person.field_id = '.$this->field_id.')',
                        'where'    => '',
                    ];
                }
                break;

            default:
                throw new \InvalidArgumentException("Invalid field: {$this->field}");
        }
    }
}
