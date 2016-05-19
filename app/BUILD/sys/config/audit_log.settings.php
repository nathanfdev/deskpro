<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\ApiKey;
use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\Entity\BanEmail;
use Application\DeskPRO\Entity\BanIp;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\CustomDefArticle;
use Application\DeskPRO\Entity\CustomDefBilling;
use Application\DeskPRO\Entity\CustomDefChat;
use Application\DeskPRO\Entity\CustomDefFeedback;
use Application\DeskPRO\Entity\CustomDefOrganization;
use Application\DeskPRO\Entity\CustomDefPerson;
use Application\DeskPRO\Entity\CustomDefProduct;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\EmailAccount;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonContactData;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\PersonNote;
use Application\DeskPRO\Entity\PhoneNumber;
use Application\DeskPRO\Entity\Problem;
use Application\DeskPRO\Entity\Product;
use Application\DeskPRO\Entity\Setting;
use Application\DeskPRO\Entity\Sla;
use Application\DeskPRO\Entity\Template;
use Application\DeskPRO\Entity\TicketCategory;
use Application\DeskPRO\Entity\TicketLayout;
use Application\DeskPRO\Entity\TicketMacro;
use Application\DeskPRO\Entity\TicketPriority;
use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\Entity\TicketWorkflow;
use Application\DeskPRO\Entity\Usergroup;
use Application\DeskPRO\Entity\Usersource;
use Application\DeskPRO\Entity\WhiteListedIp;
use DeskPRO\Bundle\AuditBundle\EventListener\AuditListener;

return [
    AgentTeam::class => [
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                ['preconditions' => ['members', 'name']],
            ],
        ],
    ],

    ApiKey::class => [
        AuditListener::ALL => [
            'field_filters' => [
                'actions' => [
                    ['collection', ['item.getAction()']],
                ],
                'person' => [
                    ['entity', ['entity.getDisplayName()~"("~entity.getId()~")"']],
                ],
            ],
        ],
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                ['preconditions' => ['person', 'note', 'flags', 'actions']],
            ],
        ],
    ],

    AppInstance::class => [
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
    ],

    BanEmail::class => [
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
    ],

    BanIp::class => [
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
    ],

    ChatConversation::class => [
        AuditListener::REMOVE => [
            'fields' => [
                'id',
                'department',
                'agent_team',
                'labels',
                'subject',
                'status',
                'agent',
                'person',
                'session',
                'visitor_id',
                'person_name',
                'person_email',
                'is_agent',
                'is_window',

            ],
        ],
        'field_filters' => [
            'labels' => [
                ['collection', 'item.getLabel()'],
            ],
        ],
    ],

    CustomDefArticle::class => [
        AuditListener::ALL => [
            'fields' => ['parent', 'title', 'description', 'options', 'is_enabled'],
        ],
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                ['preconditions' => ['parent', 'title', 'description', 'options', 'is_enabled']],
            ],
        ],
    ],

    CustomDefBilling::class => [
        AuditListener::ALL => [
            'fields' => ['parent', 'title', 'description', 'options', 'is_enabled'],
        ],
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                ['preconditions' => ['parent', 'title', 'description', 'options', 'is_enabled']],
            ],
        ],
    ],

    CustomDefChat::class => [
        AuditListener::ALL => [
            'fields' => ['parent', 'title', 'description', 'options', 'is_enabled'],
        ],
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                ['preconditions' => ['parent', 'title', 'description', 'options', 'is_enabled']],
            ],
        ],
    ],

    CustomDefFeedback::class => [
        AuditListener::ALL => [
            'fields' => ['parent', 'title', 'description', 'options', 'is_enabled'],
        ],
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                ['preconditions' => ['parent', 'title', 'description', 'options', 'is_enabled']],
            ],
        ],
    ],

    CustomDefOrganization::class => [
        AuditListener::ALL => [
            'fields' => ['parent', 'title', 'description', 'options', 'is_enabled'],
        ],
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                ['preconditions' => ['parent', 'title', 'description', 'options', 'is_enabled']],
            ],
        ],
    ],

    CustomDefPerson::class => [
        AuditListener::ALL => [
            'fields' => ['parent', 'title', 'description', 'options', 'is_enabled'],
        ],
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                ['preconditions' => ['parent', 'title', 'description', 'options', 'is_enabled']],
            ],
        ],
    ],

    CustomDefProduct::class => [
        AuditListener::ALL => [
            'fields' => ['parent', 'title', 'description', 'options', 'is_enabled'],
        ],
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                ['preconditions' => ['parent', 'title', 'description', 'options', 'is_enabled']],
            ],
        ],
    ],

    CustomDefTicket::class => [
        AuditListener::ALL => [
            'fields' => ['parent', 'title', 'description', 'options', 'is_enabled'],
        ],
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                ['preconditions' => ['parent', 'title', 'description', 'options', 'is_enabled']],
            ],
        ],
    ],

    Department::class => [
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                ['preconditions' => ['parent', 'avatar_blob', 'title', 'user_title', 'permissions']],
            ],
        ],
    ],

    EmailAccount::class => [
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                [
                    'preconditions' => [
                        'account_type',
                        'incoming_account',
                        'is_enabled',
                        'address',
                        'other_addresses',
                        'options',
                    ],
                ],
            ],
        ],
    ],

    Language::class => [
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
    ],

    Organization::class => [
        AuditListener::ALL => [
            'field_filters' => [
                'labels' => [
                    ['collection', ['item.getLabel()']],
                ],
                'email_domains' => [
                    ['collection', ['item.getDomain()']],
                ],
            ],
        ],
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                [
                    'preconditions' => [
                        'name',
                        'summary',
                        'parent',
                    ],
                ],
            ],
        ],
    ],

    Person::class => [
        AuditListener::ALL => [
            'field_filters' => [
                'password' => [
                    ['mask', ['*']],
                ],
                'labels' => [
                    ['collection', ['item.getLabel()']],
                ],
            ],
        ],
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                [
                    'preconditions' => [
                        'password',
                        'picture_blob',
                        'organization',
                        'primary_email',
                        'primary_team',
                        'is_agent',
                        'can_admin',
                        'is_deleted',
                        'is_disabled',
                        'name',
                        'first_name',
                        'last_name',
                        'summary',
                        'organization_position',
                        'organization_manager',
                        'timezone',
                        'password',
                        'usergroups',
                    ],
                ],
            ],
        ],
    ],
    PersonContactData::class => [
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                [
                    'preconditions' => [
                        'field_1',
                        'field_2',
                        'field_3',
                        'field_4',
                        'field_5',
                        'field_6',
                        'field_7',
                        'field_8',
                        'field_9',
                        'field_10',
                    ],
                ],
            ],
        ],
    ],

    PersonEmail::class => [
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                ['preconditions' => ['is_validated']],
            ],
        ],
    ],

    PersonNote::class => [
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                ['preconditions' => ['note']],
            ],
        ],
    ],

    PhoneNumber::class => [
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                ['preconditions' => ['number', 'ext', 'label', 'region']],
            ],
        ],
    ],

    Problem::class => [
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                ['preconditions' => ['title', 'is_open']],
            ],
        ],
    ],

    Product::class => [
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                ['preconditions' => ['title']],
            ],
        ],
    ],

    Setting::class => [
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                ['preconditions' => ['name', 'value']],
            ],
        ],
    ],

    Sla::class => [
        AuditListener::ALL => [
            'field_filters' => [
                'apply_terms' => [
                    ['object', ['object.serialize()']],
                ],
                'warn_actions' => [
                    ['object', ['object.serialize()']],
                ],
                'fail_actions' => [
                    ['object', ['object.serialize()']],
                ],
            ],
        ],
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => true,
    ],

    Template::class => [
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                ['preconditions' => ['template_code']],
            ],
        ],
    ],

    TicketCategory::class => [
        AuditListener::ALL => [
            'fields' => [
                'id',
                'title',
                'parent',
            ],
        ],
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                ['preconditions' => ['title']],
            ],
        ],
    ],

    TicketLayout::class => [
        AuditListener::ALL => [
            'field_filters' => [
                'agent_layout' => [
                    ['object', ['object.serialize()']],
                ],
                'user_layout' => [
                    ['object', ['object.serialize()']],
                ],
            ],
        ],
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                ['preconditions' => ['user_layout', 'agent_layout']],
            ],
        ],
    ],

    TicketMacro::class => [
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                ['preconditions' => ['title', 'is_global', 'actions']],
            ],
        ],
    ],

    TicketPriority::class => [
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => true,
    ],

    TicketTrigger::class => [
        AuditListener::ALL => [
            'field_filters' => [
                'terms' => [
                    ['object', ['object.serialize()']],
                ],
                'actions' => [
                    ['object', ['object.serialize()']],
                ],
            ],
        ],
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                [
                    'preconditions' => [
                        'department',
                        'email_account',
                        'title',
                        'is_enabled',
                        'is_hidden',
                        'is_editable',
                        'sys_name',
                        'event_trigger',
                        'event_flags',
                        'by_agent_mode',
                        'by_user_mode',
                        'by_app_mode',
                        'terms',
                        'actions',
                    ],
                ],
            ],
        ],
    ],

    TicketWorkflow::class => [
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                ['preconditions' => ['title']],
            ],
        ],
    ],

    Usergroup::class => [
        AuditListener::ALL => [
            'field_filters' => [
                'permissions' => [
                    ['collection', ['item.name']],
                ],
            ],
        ],
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                ['preconditions' => ['title', 'note', 'is_enabled', 'permissions']],
            ],
        ],
    ],

    Usersource::class => [
        AuditListener::INSERT => true,
        AuditListener::REMOVE => true,
        AuditListener::UPDATE => [
            'conditions' => [
                [
                    'preconditions' => [
                        'title',
                        'type',
                        'source_type',
                        'lost_password_url',
                        'options',
                        'is_enabled',
                        'is_sso_auto',
                        'is_sso_background',
                        'sync_enabled',
                        'auto_agent',
                        'agent_permission_group',
                        'user_permission_group',
                        'app',
                    ],
                ],
            ],
        ],
    ],

    WhiteListedIp::class => [AuditListener::INSERT => true],
];
