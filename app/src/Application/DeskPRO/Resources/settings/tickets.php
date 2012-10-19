<?php return array(
	'core_tickets.lock_timeout' => 600,
	'core_tickets.hard_delete_time' => 2419000,
	'core_tickets.spam_delete_time' => 172800,

	'core_tickets.tac_auth_code_len' => 15,
	'core_tickets.ptac_auth_code_len' => 15,

	'core_tickets.enable_feedback' => 1,
	'core_tickets.feedback_agents_read' => 1,

	'core_tickets.enable_billing' => 0,
	'core_tickets.billing_on_reply' => 0,
	'core_tickets.billing_auto_timer' => 0,
	'core_tickets.billing_currency' => 'USD',

	'core_tickets.use_ref' => false,

	'core_tickets.email_history_limit' => 11, // 10 + 1 for the original message at top

	'core.allow_arbitrary_gateway_address' => 1,

	'core_tickets.use_archive' => 0,
	'core_tickets.auto_archive_time' => 2419000,

	// True to force agent emails to have the marker line
	'core_tickets.gateway_agent_require_marker' => true,
);
