<?php return array(
	'agent_teams' => array(
		'fields' => array('name', 'members'),
	),
	'api_keys' => array(
		'fields' => array('code', 'note', 'person')
	),
	// @TODO - need to return to this, should work, for now throws {"error_code":"http_error.500","error_message":"Method `getid` is undefined"}
	/*'ban_emails' => array(
		'fields' => array('banned_email')
	),
	'ban_ips' => array(
		'fields' => array('banned_ip')
	),*/
	'custom_def_ticket' => array(
		'fields' => array('title', 'description', 'handler_class', 'options', 'is_user_enabled', 'is_enabled', 'default_value', 'is_agent_field')
	),
	'departments' => array(
		'fields' => array('title', 'user_title', 'parent', 'display_order', 'email_gateway')
	),
	'department_permissions' => array(
		'fields'  => array('app', 'name', 'value', 'usergroup', 'person'),
		'save_as_change' => array(
			'object_field_id' => 'department',
			'as_field_id' => 'permissions',
			'render_value' => function($obj, $op) {
				$op_word = $op == 'create' ? 'ADD: ' : 'REMOVE: ';
				if ($obj->person) {
					return "$op_word{$obj->name} for people@{$obj->person->getId()} -- {$obj->person->getDisplayContact()}";
				} else {
					return "$op_word{$obj->name} for usergroups@{$obj->usergroup->getId()} -- {$obj->usergroup->title}";
				}
			}
		),
	),
	'email_gateways' => array(
		'fields' => array('department', 'connection_type', 'connection_options', 'is_enabled')
	),
	'email_gateway_addresses' => array(
		'fields' => array('match_pattern'),
		'save_as_change' => array(
			'object_field_id' => 'gateway',
			'as_field_id'     => 'email_address',
			'render_value'    => function($obj, $op) {
				$op_word = $op == 'create' ? 'ADD: ' : 'REMOVE: ';
				return "$op_word{$obj->match_pattern}";
			}
		)
	),
	'email_transports' => array(
		'fields' => array('match_pattern', 'transport_type', 'transport_options'),
	),
	/*'label_defs' => array(
		'fields' => array('label_type', 'label'),
	),*/
	'languages' => array(
		'fields' => array('title'),
	),
	'permissions' => array(
		'fields'  => array('usergroup', 'person', 'name', 'value'),
		'save_as_change' => array(
			'object_field_id' => function($obj) {
				if ($obj->usergroup) {
					return $obj->usergroup;
				} else {
					return $obj->person;
				}
			},
			'as_field_id' => 'permissions',
			'render_value' => function($obj, $op) {
				$op_word = $op == 'create' ? 'ADD: ' : 'REMOVE: ';
				if ($obj->person) {
					return "$op_word{$obj->name}";
				} else {
					return "$op_word{$obj->name}";
				}
			}
		),
	),
	'people' => array(
		'fields' => array('is_agent', 'was_agent', 'can_admin', 'can_billing', 'can_reports', 'is_deleted', 'is_disabled', 'name', 'first_name', 'last_name', 'password'),
		'do_log_check' => function($obj) {
			if ($obj['is_agent'] || $obj['was_agent']) {
				return true;
			}
			return false;
		}
	),
	'people_emails' => array(
		'fields' => array('email', 'is_validated'),
		'save_as_change' => array(
			'object_field_id' => 'person',
			'as_field_id'     => 'emails',
			'render_value'    => function($obj, $op) {
				$op_word = $op == 'create' ? 'ADD: ' : 'REMOVE: ';
				return "$op_word{$obj->email}";
			}
		)
	),
	'phrasesxxx' => array(
		'fields' => array('language', 'name', 'phrase'),
		'save_as_change'      => array(
			'object_field_id' => 'language',
			'as_field_id'     => 'phrase',
			'render_value'    => function($obj, $op) {
				return "{$obj->name} = {$obj->phrase}";
			}
		)
	),
	'products' => array(
		'fields' => array('title', 'parent', 'display_order')
	),
	'settings' => array(
		'fields' => array('name', 'value')
	),
	'slas' => array(
		'fields' => array('warning_trigger', 'fail_trigger', 'apply_priority', 'apply_trigger', 'title', 'sla_type', 'active_time', 'work_start', 'work_end', 'work_days', 'work_timezone', 'work_holidays', 'apply_type'),
	),
	'templates' => array(
		'fields' => array('name', 'template_code'),
	),
	'ticket_categories' => array(
		'fields' => array('title', 'parent', 'display_order')
	),
	'ticket_priorities' => array(
		'fields' => array('title', 'priority')
	),
	'ticket_workflows' => array(
		'fields' => array('title', 'display_order')
	),
	'usergroups' => array(
		'fields' => array('title', 'note', 'is_enabled')
	),
	'usersource_plugins' => array(
		'fields' => array('title')
	),
	'usersources' => array(
		'fields' => array('title', 'options', 'is_enabled')
	),
	'widgets' => array(
		'fields' => array('description', 'title', 'html', 'js', 'css', 'page', 'page_location', 'insert_position', 'enabled'),
	)
);