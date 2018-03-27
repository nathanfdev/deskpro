<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Reports\Overview;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Brand;
use DeskPRO\Component\Util\MapUtils;
use Orb\Util\Arrays;

class GroupingField
{
    const BRAND           = 'brand';
    const DEPARTMENT      = 'department';
    const TICKET_CATEGORY = 'ticket_category';
    const TICKET_WORKFLOW = 'ticket_workflow';
    const TICKET_PRIORITY = 'ticket_priority';
    const LANGUAGE        = 'language';
    const PRODUCT         = 'product';
    const TICKET_FIELD    = 'ticket_field';
    const USER_FIELD      = 'user_field';
    const AGENT           = 'agent';
    const AGENT_TEAM      = 'agent_team';
    const TICKET_URGENCY  = 'ticket_urgency';
    const ORGANIZATION    = 'organization';
    const USER            = 'user';
    const USERGROUP       = 'usergroup';

    /**
     * @var string
     */
    protected $field;

    /**
     * @var int
     */
    protected $field_id;

    /**
     * @var null
     */
    protected $titles = null;

    /**
     * @param string $field
     */
    public function __construct($field)
    {
        if (strpos($field, '.') === false) {
            $this->field = $field;
        } else {
            list($field, $field_id) = explode('.', $field);
            $this->field            = $field;
            $this->field_id         = $field_id;

            // grouping field is a custom field
            // we need to check that it exists.
            // ideally we'd throw here, but as backwards compat
            // we will just fallback
            switch ($this->field) {
                case self::TICKET_FIELD:
                    $fm = App::getSystemService('ticket_fields_manager');
                    break;
                case self::USER_FIELD:
                    $fm = App::getSystemService('person_fields_manager');
                    break;
                default:
                    $fm = null;
            }

            if ($fm) {
                $f = $fm->getFieldFromId($this->field_id);
                if (!$f) {
                    $this->field    = $this->getDefaultField();
                    $this->field_id = null;
                }
            }
        }
    }

    /**
     * Used as the default if the specified field is invalid.
     *
     * @return string
     */
    protected function getDefaultField()
    {
        return self::DEPARTMENT;
    }

    public function getFieldInfo()
    {
        switch ($this->field) {
            case self::BRAND:
                return ['select'   => 'COALESCE(tickets.brand_id, 0) AS group_field',
                        'group_by' => 'group_field',
                        'join'     => '',
                        'where'    => '',
                ];
                break;

            case self::DEPARTMENT:
                return ['select'   => 'COALESCE(tickets.department_id, 0) AS group_field',
                        'group_by' => 'group_field',
                        'join'     => '',
                        'where'    => '',
                ];
                break;

            case self::AGENT:
                return ['select'   => 'COALESCE(tickets.agent_id, 0) AS group_field',
                        'group_by' => 'group_field',
                        'join'     => '',
                        'where'    => '',
                ];
                break;

            case self::AGENT_TEAM:
                return ['select'   => 'COALESCE(tickets.agent_team_id, 0) AS group_field',
                        'group_by' => 'group_field',
                        'join'     => '',
                        'where'    => '',
                ];
                break;

            case self::TICKET_CATEGORY:
                return ['select'   => 'COALESCE(tickets.category_id, 0) AS group_field',
                        'group_by' => 'group_field',
                        'join'     => '',
                        'where'    => '',
                ];
                break;

            case self::TICKET_WORKFLOW:
                return ['select'   => 'COALESCE(tickets.workflow_id, 0) AS group_field',
                        'group_by' => 'group_field',
                        'join'     => '',
                        'where'    => '',
                ];
                break;

            case self::TICKET_PRIORITY:
                return ['select'   => 'COALESCE(tickets.priority_id, 0) AS group_field',
                        'group_by' => 'group_field',
                        'join'     => '',
                        'where'    => '',
                ];
                break;

            case self::LANGUAGE:
                return ['select'   => 'tickets.language_id',
                        'group_by' => 'tickets.language_id',
                        'join'     => '',
                        'where'    => '',
                ];
                break;

            case self::PRODUCT:
                return ['select'   => 'COALESCE(tickets.product_id, 0) AS group_field',
                        'group_by' => 'group_field',
                        'join'     => '',
                        'where'    => '',
                ];
                break;

            case self::TICKET_URGENCY:
                return ['select'   => 'tickets.urgency',
                        'group_by' => 'tickets.urgency',
                        'join'     => '',
                        'where'    => '',
                ];
                break;

            case self::ORGANIZATION:
                return ['select'   => 'COALESCE(tickets.organization_id, 0) AS org_id',
                        'group_by' => 'org_id',
                        'join'     => '',
                        'where'    => '',
                ];
                break;

            case self::USERGROUP:
                return [
                    'select'   => 'COALESCE(person2usergroups.usergroup_id, 0) AS usergroup_id',
                    'group_by' => 'usergroup_id',
                    'join'     => 'LEFT JOIN person2usergroups ON (person2usergroups.person_id = tickets.person_id)',
                    'where'    => '',
                ];
                break;

            case self::USER:
                return ['select'   => 'tickets.person_id',
                        'group_by' => 'tickets.person_id',
                        'join'     => '',
                        'where'    => '',
                ];
                break;

            case self::TICKET_FIELD:
                $field_def = App::getSystemService('ticket_fields_manager')->getFieldFromId($this->field_id);

                if ($field_def->isChoiceType()) {
                    $children = App::getSystemService('ticket_fields_manager')->getFieldChildren($field_def);
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
                        'select'   => 'COALESCE(custom_def_ticket.title, 0) AS group_field',
                        'group_by' => 'group_field',
                        'join'     => "
                            LEFT JOIN custom_data_ticket ON (custom_data_ticket.ticket_id = tickets.id AND custom_data_ticket.field_id IN($ids))
                            LEFT JOIN custom_def_ticket ON (custom_def_ticket.id = custom_data_ticket.field_id)
                        ",
                        'where' => '',
                    ];
                } else {
                    return [
                        'select'   => 'COALESCE(custom_data_ticket.input, 0) AS group_field',
                        'group_by' => 'group_field',
                        'join'     => 'LEFT JOIN custom_data_ticket ON (custom_data_ticket.ticket_id = tickets.id AND custom_data_ticket.field_id = '.$this->field_id.')',
                        'where'    => '',
                    ];
                }
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
                        'select'   => 'COALESCE(custom_def_people.title, 0) AS group_field',
                        'group_by' => 'group_field',
                        'join'     => "
                            LEFT JOIN custom_data_person ON (custom_data_person.person_id = tickets.person_id AND custom_data_person.field_id IN($ids))
                            LEFT JOIN custom_def_people ON (custom_def_people.id = custom_data_person.field_id)
                        ",
                        'where' => '',
                    ];
                } else {
                    return [
                        'select'   => 'COALESCE(custom_data_person.input, 0) AS group_field',
                        'group_by' => 'group_field',
                        'join'     => 'LEFT JOIN custom_data_person ON (custom_data_person.person_id = tickets.person_id AND custom_data_person.field_id = '.$this->field_id.')',
                        'where'    => '',
                    ];
                }
                break;

            default:
                throw new \InvalidArgumentException("Invalid field: {$this->field}");
        }
    }

    /**
     * @param array $values
     *
     * @throws \InvalidArgumentException
     *
     * @return array|null
     */
    public function getTitles(array $values = [])
    {
        if ($this->titles !== null) {
            return $this->titles;
        }

        switch ($this->field) {
            case self::BRAND:
                $brands       = App::getEntityRepository(Brand::class)->findAll();
                $this->titles = MapUtils::map($brands, function ($key, $value) {
                    /* @var Brand $value */
                    return [$value->getId(), $value->getName()];
                });
                break;

            case self::DEPARTMENT:
                $this->titles = App::getDataService('Department')->getFullNames('tickets');
                break;

            case self::AGENT:
                $this->titles = App::getDataService('Person')->getAgentNames();
                break;

            case self::AGENT_TEAM:
                $this->titles = App::getDataService('AgentTeam')->getTeamNames();
                break;

            case self::TICKET_CATEGORY:
                $this->titles = App::getDataService('TicketCategory')->getFullNames();
                break;

            case self::TICKET_WORKFLOW:
                $this->titles = App::getDataService('TicketWorkflow')->getNames();
                break;

            case self::TICKET_PRIORITY:
                $this->titles = App::getDataService('TicketPriority')->getNames();
                break;

            case self::LANGUAGE:
                $this->titles = App::getDataService('Language')->getTitles();
                break;

            case self::PRODUCT:
                $this->titles = App::getDataService('Product')->getFullNames();
                break;

            case self::TICKET_URGENCY:
                $this->titles = array_combine(range(1, 10), range(1, 10));
                break;

            case self::ORGANIZATION:

                if ($values) {
                    $names = App::getOrm()->getRepository('DeskPRO:Organization')->getOrganizationNames(
                        array_keys($values)
                    );
                } else {
                    $names = [];
                }

                Arrays::unshiftAssoc($names, '0', 'No Organization');

                $this->titles = $names;

                break;

            case self::USERGROUP:
                $names = App::getDataService('Usergroup')->getUsergroupNames();
                Arrays::unshiftAssoc($names, '0', 'No Usergroup');
                $this->titles = $names;
                break;

            case self::USER:

                $names = [];

                if ($values) {
                    $people = App::getOrm()->getRepository('DeskPRO:Person')->getByIds(array_keys($values));
                    foreach ($people as $p) {
                        $names[$p->getId()] = $p->getDisplayContact();
                    }
                }

                $this->titles = $names;
                break;

            case self::TICKET_FIELD:

                if ($values) {
                    $names = array_combine(array_keys($values), array_keys($values));
                    unset($names[0]);
                    $this->titles = $names;
                } else {
                    $this->titles = [];
                }
                break;

            case self::USER_FIELD:

                if ($values) {
                    $names = array_combine(array_keys($values), array_keys($values));
                    unset($names[0]);
                    $this->titles = $names;
                } else {
                    $this->titles = [];
                }
                break;

            default:
                throw new \InvalidArgumentException("Invalid field: {$this->field}");
        }

        if (!isset($this->titles[0])) {
            Arrays::unshiftAssoc($this->titles, '0', 'None');
        }

        return $this->titles;
    }
}
