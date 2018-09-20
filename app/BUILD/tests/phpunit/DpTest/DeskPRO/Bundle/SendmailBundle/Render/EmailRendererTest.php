<?php

namespace DpTest\SendmailBundle\Render;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use DeskPRO\Bundle\SendmailBundle\Render\EmailRenderer;
use DpTest\SendmailTestCase;

class EmailRendererTest extends SendmailTestCase
{
    /**
     * @var EmailRenderer
     */
    private $renderer;
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->getEntityManager()->clear();
        $this->renderer = $this->get('email.email_renderer');
    }

    public function testGetStructure()
    {
        $person = new Person();

        $ticket = new Ticket();
        $ticket->disableAutoTicketProcess();
        $ticket->setSubject('subject');

        $this->getEntityManager()->persist($person);
        $this->getEntityManager()->persist($ticket);
        $this->getEntityManager()->flush();
        $ticket->setPerson($person);

        $message = new TicketMessage();
        $message->setPerson($person);
        $ticket->addMessage($message);

        $this->getEntityManager()->persist($message);
        $this->getEntityManager()->flush();

        $viewModel = $this->get('email.user_viewmodel_factory')
            ->createTicketReplyByAgentModel(
                $ticket,
                $message
            );
        $structure = $this->renderer->getStructure($viewModel);
        $this->assertEquals($this->getStructure(), $structure);
    }

    protected function getStructure()
    {
        return [
            'recipient' => [
                'description' => 'Email recipient.',
                'type'        => 'object (Person)',
                'attribute'   => 'recipient',
                'properties'  => [
                    'id' => [
                        'description' => 'The unique ID of person.',
                        'type'        => 'integer',
                        'attribute'   => 'id',
                    ],
                    'first_name' => [
                        'description' => 'The users name (best guess from other sources etc).',
                        'type'        => 'string',
                        'attribute'   => 'first_name',
                    ],
                    'last_name' => [
                        'description' => 'The users name (best guess from other sources etc).',
                        'type'        => 'string',
                        'attribute'   => 'last_name',
                    ],
                    'name' => [
                        'description' => 'The users name (best guess from other sources etc).',
                        'type'        => 'string',
                        'attribute'   => 'name',
                    ],
                    'display_name' => [
                        'description' => 'Person name.',
                        'type'        => 'string',
                        'attribute'   => 'display_name',
                    ],
                    'online' => [
                        'description' => 'Is user online?',
                        'type'        => 'boolean',
                        'attribute'   => 'online',
                    ],
                    'last_seen' => [
                        'description' => 'Date when user was last seen online.',
                        'type'        => 'DateTime',
                        'attribute'   => 'last_seen',
                    ],
                    'disable_picture' => [
                        'description' => 'True if user`s picture disabled.',
                        'type'        => 'boolean',
                        'attribute'   => 'disable_picture',
                    ],
                    'gravatar_url' => [
                        'description' => 'The URL to the users gravatar if any.',
                        'type'        => 'string',
                        'attribute'   => 'gravatar_url',
                    ],
                    'is_contact' => [
                        'description' => 'Is this person a contact?',
                        'type'        => 'boolean',
                        'attribute'   => 'is_contact',
                    ],
                    'was_agent' => [
                        'description' => 'Was this person an agent?',
                        'type'        => 'boolean',
                        'attribute'   => 'was_agent',
                    ],
                    'can_agent' => [
                        'description' => 'Is person allowed to use agent interface.',
                        'type'        => 'boolean',
                        'attribute'   => 'can_agent',
                    ],
                    'can_admin' => [
                        'description' => 'Is person allowed to use admin interface.',
                        'type'        => 'boolean',
                        'attribute'   => 'can_admin',
                    ],
                    'can_billing' => [
                        'description' => 'Is person allowed to use billing interface.',
                        'type'        => 'boolean',
                        'attribute'   => 'can_billing',
                    ],
                    'disable_autoresponses' => [
                        'description' => 'Are autoresponses disabled?',
                        'type'        => 'boolean',
                        'attribute'   => 'disable_autoresponses',
                    ],
                    'disable_autoresponses_log' => [
                        'description' => 'Disabled autoresponses log.',
                        'type'        => 'string',
                        'attribute'   => 'disable_autoresponses_log',
                    ],
                    'is_confirmed' => [
                        'description' => 'Has person confirmed their email?',
                        'type'        => 'boolean',
                        'attribute'   => 'is_confirmed',
                    ],
                    'is_deleted' => [
                        'description' => 'Is the user deleted?',
                        'type'        => 'boolean',
                        'attribute'   => 'is_deleted',
                    ],
                    'is_disabled' => [
                        'description' => 'Is the user disabled?',
                        'type'        => 'boolean',
                        'attribute'   => 'is_disabled',
                    ],
                    'creation_system' => [
                        'description' => 'The way person was created.',
                        'type'        => 'string',
                        'attribute'   => 'creation_system',
                    ],
                    'override_display_name' => [
                        'description' => 'Overrides the display name of an person in the user interface (agents only).',
                        'type'        => 'string',
                        'attribute'   => 'override_display_name',
                    ],
                    'display_contact' => [
                        'description' => 'Person name and email address.',
                        'type'        => 'string',
                        'attribute'   => 'display_contact',
                    ],
                    'summary' => [
                        'description' => 'The summary field as filled in by agents.',
                        'type'        => 'string',
                        'attribute'   => 'summary',
                    ],
                    'organization_position' => [
                        'description' => 'The persons position at the organization.',
                        'type'        => 'string',
                        'attribute'   => 'organization_position',
                    ],
                    'organization_manager' => [
                        'description' => 'True if the person is a manager of their organization.',
                        'type'        => 'boolean',
                        'attribute'   => 'organization_manager',
                    ],
                    'timezone' => [
                        'description' => 'The timezone associated with this user.',
                        'type'        => 'string',
                        'attribute'   => 'timezone',
                    ],
                    'date_created' => [
                        'description' => 'The date the user was inserted into the system.',
                        'type'        => 'DateTime',
                        'attribute'   => 'date_created',
                    ],
                    'date_last_login' => [
                        'description' => 'The date the user was logged in last time.',
                        'type'        => 'DateTime',
                        'attribute'   => 'date_last_login',
                    ],
                    'browser' => [
                        'description' => 'The browser person was used last time.',
                        'type'        => 'string',
                        'attribute'   => 'browser',
                    ],
                    'tickets_count' => [
                        'description' => 'Overall tickets count assigned to user.',
                        'type'        => 'integer',
                        'attribute'   => 'tickets_count',
                    ],
                    'chats_count' => [
                        'description' => 'Overall count of chats user participating.',
                        'type'        => 'integer',
                        'attribute'   => 'chats_count',
                    ],
                    'contact_data' => [
                        'description' => 'Contacts for this user.',
                        'type'        => 'array',
                        'attribute'   => 'contact_data',
                    ],
                    'is_agent' => [
                        'description' => 'True if person is agent.',
                        'type'        => 'boolean',
                        'attribute'   => 'is_agent',
                    ],
                    'online_for_chat' => [
                        'description' => 'Is user online for chat?',
                        'type'        => 'boolean',
                        'attribute'   => 'online_for_chat',
                    ],
                    'can_reports' => [
                        'description' => 'Is person allowed to use reports interface.',
                        'type'        => 'boolean',
                        'attribute'   => 'can_reports',
                    ],
                ],
            ],
            'site_url' => [
                'description' => 'Site Url.',
                'type'        => 'string',
                'attribute'   => 'site_url',
            ],
            'site_name' => [
                'description' => 'Site Name.',
                'type'        => 'string',
                'attribute'   => 'site_name',
            ],
            'deskpro_url' => [
                'description' => 'DeskPro Url.',
                'type'        => 'string',
                'attribute'   => 'deskpro_url',
            ],
            'ticket' => [
                'description' => 'The ticket.',
                'type'        => 'object (Ticket)',
                'attribute'   => 'ticket',
                'properties'  => [
                    'id' => [
                        'description' => 'The unique ID.',
                        'type'        => 'integer',
                        'attribute'   => 'id',
                    ],
                    'ref' => [
                        'description' => 'String reference.',
                        'type'        => 'string',
                        'attribute'   => 'ref',
                    ],
                    'auth' => [
                        'description' => 'Auth string.',
                        'type'        => 'integer',
                        'attribute'   => 'auth',
                    ],
                    'sent_to_address' => [
                        'description' => 'Addresses where ticket was send.',
                        'type'        => 'array',
                        'attribute'   => 'sent_to_address',
                    ],
                    'email_account_address' => [
                        'description' => 'Email account address used to gather the ticket.',
                        'type'        => 'string',
                        'attribute'   => 'email_account_address',
                    ],
                    'creation_system' => [
                        'description' => 'How this ticket appears.',
                        'type'        => 'string',
                        'attribute'   => 'creation_system',
                    ],
                    'creation_system_option' => [
                        'description' => 'Option used by creation system.',
                        'type'        => 'string',
                        'attribute'   => 'creation_system_option',
                    ],
                    'ticket_hash' => [
                        'description' => 'Unique hash.',
                        'type'        => 'string',
                        'attribute'   => 'ticket_hash',
                    ],
                    'status' => [
                        'description' => 'Legacy ticket status.',
                        'type'        => 'string',
                        'attribute'   => 'status',
                    ],
                    'hidden_status' => [
                        'description' => 'Legacy ticket hidden status.',
                        'type'        => 'string',
                        'attribute'   => 'hidden_status',
                    ],
                    'is_hold' => [
                        'description' => 'Is this on hold?',
                        'type'        => 'boolean',
                        'attribute'   => 'is_hold',
                    ],
                    'urgency' => [
                        'description' => 'How urgent this ticket?',
                        'type'        => 'integer',
                        'attribute'   => 'urgency',
                    ],
                    'feedback_rating' => [
                        'description' => 'It\'s rating based on feedback votes.',
                        'type'        => 'integer',
                        'attribute'   => 'feedback_rating',
                    ],
                    'date_feedback_rating' => [
                        'description' => 'When the rating was calculated.',
                        'type'        => 'DateTime',
                        'attribute'   => 'date_feedback_rating',
                    ],
                    'date_created' => [
                        'description' => 'When the ticket was created.',
                        'type'        => 'DateTime',
                        'attribute'   => 'date_created',
                    ],
                    'date_resolved' => [
                        'description' => 'When the ticket was resolved.',
                        'type'        => 'DateTime',
                        'attribute'   => 'date_resolved',
                    ],
                    'date_archived' => [
                        'description' => 'And archived at last.',
                        'type'        => 'DateTime',
                        'attribute'   => 'date_archived',
                    ],
                    'date_first_agent_assign' => [
                        'description' => 'When it was assigned to agent at the very first time.',
                        'type'        => 'DateTime',
                        'attribute'   => 'date_first_agent_assign',
                    ],
                    'date_first_agent_reply' => [
                        'description' => 'When it was first time replied.',
                        'type'        => 'DateTime',
                        'attribute'   => 'date_first_agent_reply',
                    ],
                    'date_last_agent_reply' => [
                        'description' => 'And when it was replied the last time.',
                        'type'        => 'DateTime',
                        'attribute'   => 'date_last_agent_reply',
                    ],
                    'date_last_user_reply' => [
                        'description' => 'And the date user replied here last time.',
                        'type'        => 'DateTime',
                        'attribute'   => 'date_last_user_reply',
                    ],
                    'date_agent_waiting' => [
                        'description' => 'Time when agent started to wait user.',
                        'type'        => 'DateTime',
                        'attribute'   => 'date_agent_waiting',
                    ],
                    'date_user_waiting' => [
                        'description' => 'And the time when user started to wait agent.',
                        'type'        => 'DateTime',
                        'attribute'   => 'date_user_waiting',
                    ],
                    'date_status' => [
                        'description' => 'When status was changed.',
                        'type'        => 'DateTime',
                        'attribute'   => 'date_status',
                    ],
                    'total_user_waiting' => [
                        'description' => 'How much user was waited?',
                        'type'        => 'integer',
                        'attribute'   => 'total_user_waiting',
                    ],
                    'total_to_first_reply' => [
                        'description' => 'Total waiting before first reply.',
                        'type'        => 'integer',
                        'attribute'   => 'total_to_first_reply',
                    ],
                    'date_locked' => [
                        'description' => 'Date time when ticket was locked.',
                        'type'        => 'DateTime',
                        'attribute'   => 'date_locked',
                    ],
                    'has_attachments' => [
                        'description' => 'Does this ticked has attachments?',
                        'type'        => 'boolean',
                        'attribute'   => 'has_attachments',
                    ],
                    'subject' => [
                        'description' => 'Ticket subject.',
                        'type'        => 'string',
                        'attribute'   => 'subject',
                    ],
                    'original_subject' => [
                        'description' => 'Original subject given by creator.',
                        'type'        => 'string',
                        'attribute'   => 'original_subject',
                    ],
                    'properties' => [
                        'description' => 'Array of properties.',
                        'type'        => 'array',
                        'attribute'   => 'properties',
                    ],
                    'count_agent_replies' => [
                        'description' => 'Count of all replies made by agents.',
                        'type'        => 'integer',
                        'attribute'   => 'count_agent_replies',
                    ],
                    'count_user_replies' => [
                        'description' => 'Count of all replies made by user.',
                        'type'        => 'integer',
                        'attribute'   => 'count_user_replies',
                    ],
                    'worst_sla_status' => [
                        'description' => 'Well, this is worst SLA status.',
                        'type'        => 'string',
                        'attribute'   => 'worst_sla_status',
                    ],
                    'waiting_times' => [
                        'description' => 'An array of waiting times.',
                        'type'        => 'array',
                        'attribute'   => 'waiting_times',
                    ],
                    'star' => [
                        'description' => 'Person star color.',
                        'type'        => 'string',
                        'attribute'   => 'star',
                    ],
                    'date_on_hold' => [
                        'description' => 'Datetime when ticket was set on hold.',
                        'type'        => 'DateTime',
                        'attribute'   => 'date_on_hold',
                    ],
                ],
            ],
            'ticket_person' => [
                'description' => 'The person who opened the ticket.',
                'type'        => 'object (Person)',
                'attribute'   => 'ticket_person',
            ],
            'ticket_agent' => [
                'description' => 'The agent assigned to the ticket.',
                'type'        => 'object (Person)',
                'attribute'   => 'ticket_agent',
            ],
            'ticket_link' => [
                'description' => 'A link to the ticket.',
                'type'        => 'string',
                'attribute'   => 'ticket_link',
            ],
            'reply' => [
                'description' => 'Reply written by the agent.',
                'type'        => 'object (TicketMessage)',
                'attribute'   => 'reply',
            ],
            'ticket_messages' => [
                'description' => '',
                'type'        => 'array of objects (TicketMessage)',
                'attribute'   => 'ticket_messages',
                'properties'  => [
                    'id' => [
                        'description' => 'The unique ID.',
                        'type'        => 'integer',
                        'attribute'   => 'id',
                    ],
                    'date_created' => [
                        'description' => 'Date when message was created.',
                        'type'        => 'DateTime',
                        'attribute'   => 'date_created',
                    ],
                    'is_agent_note' => [
                        'description' => 'Is this message agent note?',
                        'type'        => 'integer',
                        'attribute'   => 'is_agent_note',
                    ],
                    'creation_system' => [
                        'description' => 'How this message was created.',
                        'type'        => 'string',
                        'attribute'   => 'creation_system',
                    ],
                    'ip_address' => [
                        'description' => 'An ip address from which message was sent.',
                        'type'        => 'string',
                        'attribute'   => 'ip_address',
                    ],
                    'visitor_id' => [
                        'description' => 'Unique ID of visitor left this message.',
                        'type'        => 'string',
                        'attribute'   => 'visitor_id',
                    ],
                    'hostname' => [
                        'description' => 'Host from which message was left.',
                        'type'        => 'string',
                        'attribute'   => 'hostname',
                    ],
                    'geo_country' => [
                        'description' => 'Country message is from.',
                        'type'        => 'string',
                        'attribute'   => 'geo_country',
                    ],
                    'email' => [
                        'description' => 'The email address the user sent the email from (gateway messages only).
This is a perm record and doesnt change even if the user changes/deletes their email
address.',
                        'type'      => 'string',
                        'attribute' => 'email',
                    ],
                    'message_hash' => [
                        'description' => 'An unique hash of message.',
                        'type'        => 'string',
                        'attribute'   => 'message_hash',
                    ],
                    'message' => [
                        'description' => 'The message, will be in HTML!',
                        'type'        => 'string',
                        'attribute'   => 'message',
                    ],
                    'message_full' => [
                        'description' => 'This is the full message, including all quotes/cut content.
This will still be the HTMLPurifier\'ed content (so it\'s safe),
it\'s just the message before it\'s been run through the cutter.',
                        'type'      => 'string',
                        'attribute' => 'message_full',
                    ],
                    'message_raw' => [
                        'description' => 'This is the full raw message content. It has not been passed through
any HTML cleaning process.s.',
                        'type'      => 'string',
                        'attribute' => 'message_raw',
                    ],
                    'show_full_hint' => [
                        'description' => 'A hint to say if we should show message_full by default. We do this when
we detect that the user has replied to a message inline rather than above the cut line.',
                        'type'      => 'boolean',
                        'attribute' => 'show_full_hint',
                    ],
                    'lang_code' => [
                        'description' => 'The set/detected lang code.',
                        'type'        => 'string',
                        'attribute'   => 'lang_code',
                    ],
                    'message_preview_text' => [
                        'description' => 'This is a preview of the message.',
                        'type'        => 'string',
                        'attribute'   => 'message_preview_text',
                    ],
                ],
            ],
        ];
    }
}
