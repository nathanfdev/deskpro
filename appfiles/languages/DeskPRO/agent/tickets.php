<?php return array(

	// Statuses shown to techs
	'agent.tickets.status_awaiting_agent' => 'Awaiting Agent',
	'agent.tickets.status_pending'        => 'Awaiting User',
	'agent.tickets.status_hidden'         => 'Hidden',
	'agent.tickets.status_resolved'       => 'Resolved',
	'agent.tickets.status_closed'         => 'Closed',

	'agent.tickets.status_hidden_spam'        => 'Spam',
	'agent.tickets.status_hidden_deleted'     => 'Deleted',
	'agent.tickets.status_hidden_validating'  => 'Awaiting Validating',

	// Depreciated: Use the above ids
	'agent.tickets.hidden_status_spam'       => '[[Spam]]',
	'agent.tickets.hidden_status_deleted'    => '[[Deleted]]',
	'agent.tickets.hidden_status_validating' => '[[Awaiting Validation]]',

	'agent.tickets.creation_system_web'        => 'Web interface',
	'agent.tickets.creation_system_web_person' => 'Web interface by user',
	'agent.tickets.creation_system_web_agent'  => 'Web interface by agent',
	'agent.tickets.creation_system_gateway'    => 'Email',

	// Names of ticket fields
	'agent.tickets.urgency'                => 'Urgency',
	'agent.tickets.subject'                => 'Subject',
	'agent.tickets.category'               => 'Category',
	'agent.tickets.status'                 => 'Status',
	'agent.tickets.hidden_status'          => 'Hidden Status',
	'agent.tickets.agent'                  => 'Agent',
	'agent.tickets.agent_team'             => 'Agent Team',
	'agent.tickets.department'             => 'Department',
	'agent.tickets.product'                => 'Product',
	'agent.tickets.workflow'               => 'Workflow',
	'agent.tickets.priority'               => 'Priority',
	'agent.tickets.organization'           => 'Organization',
	'agent.tickets.id'                     => 'ID',
	'agent.tickets.date_user_waiting'      => 'User Waiting',
	'agent.tickets.creation_system'        => 'Creation system',
	'agent.tickets.receiving_gateway'      => 'Receiving gateway',

	'agent.tickets.participants'      => 'Participants',

	'agent.tickets.ticket_is_deleted' => 'Ticket is deleted',

	'agent.tickets.date_created'                      => 'Date Created',
	'agent.tickets.date_closed'                       => 'Date Closed',
	'agent.tickets.date_resolved'                     => 'Date Resolved',
	'agent.tickets.date_first_agent_reply'            => 'Date of First Agent Reply',
	'agent.tickets.date_last_user_reply'              => 'Date of Last User Reply',
	'agent.tickets.date_last_agent_reply'             => 'Date of Last Agent Reply',

	'agent.tickets.modify_ticket'          => 'Modify Ticket',
	'agent.tickets.manage_participants'    => 'Manage people on this ticket',

	'agent.tickets.filter_agent'       => 'My Tickets',
	'agent.tickets.filter_agent_team'  => 'My Team\'s Tickets',
	'agent.tickets.filter_agent_teams' => 'My Teams\' Tickets',
	'agent.tickets.filter_participant' => 'Tickets I Follow',
	'agent.tickets.filter_unassigned'  => 'Unassigned Tickets',
	'agent.tickets.filter_all'         => 'All Tickets',

	'agent.tickets.results_num_summary' => 'Showing {{current}} of {{count}} ticket|Showing {{current}} of {{count}} tickets',

	'agent.tickets.set_awaiting_user' => 'Set awaiting user',
	'agent.tickets.set_awaiting_agent' => 'Set awaiting agent',
	'agent.tickets.set_resolved' => 'Set resolved',

	'agent.tickets.tickets_on_hold' => 'Tickets on hold',
	'agent.tickets.inbox' => 'Inbox',
	'agent.tickets.filters' => 'Filters',
	'agent.tickets.other_open_tickets' => 'Tickets you currently have open',
	'agent.tickets.ticket_by_user' => 'Tickets by the same user',

	'agent.tickets.merge_none' => 'The user has no other tickets, and you don\'t have any other ticket tabs open. To merge this ticket into another one, try finding it and opening it in a tab first.',

	'agent.tickets.message_id' => 'Message ID',
	'agent.tickets.ticket_id' => 'Ticket ID',
	'agent.ticekts.ticket_ref' => 'Ticket Ref',
	'agent.tickets.creation_system' => 'Creation System',
	'agent.tickets.gateway_source_id' => 'Gateway Source ID',
	'agent.tickets.sent_from_address' => 'Send From Address',
	'agent.tickets.split_from_here' => 'Split ticket from here',
	'agent.tickets.close_tab_after_reply' => 'Close this ticket tab after reply',
	'agent.tickets.keep_current_status' => 'Keep current status',

	'agent.tickets.in_use_by' => 'This ticket is in use by: ',
	'agent.tickets.deleted_by' => 'This ticket was deleted by: ',
	'agent.tickets.perm_delete_note' => 'This ticket will be permanantly purged from the system in {{time}}',
	'agent.tickets.hidden_because' => 'This ticket is currently hidden because: ',
	'agent.tickets.hidden_explain' => 'This means it won\'t show up in most search results and it will not generate agent notifications.',

	'agent.tickets.set_hold' => 'Set on hold',
	'agent.tickets.on_hold' => 'On hold',
	'agent.tickets.user_waiting_x_and_total_y' => 'User has been waiting <time>{{waiting}}</time> for a reply and a total of <time>{{total}}</time> since the ticket started.',
	'agent.tickets.ticket_log' => 'Ticket Log',
);
