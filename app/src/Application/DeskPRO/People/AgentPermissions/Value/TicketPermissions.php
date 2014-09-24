<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category People
 */

namespace Application\DeskPRO\People\AgentPermissions\Value;

class TicketPermissions implements PermissionValueInterface
{
	public $use                                  = false;
	public $create                               = false;
	public $reply_mass                           = false;
	public $modify_set_closed                    = false;
	public $reply_own                            = false;
	public $modify_own                           = false;
	public $modify_department_own                = false;
	public $modify_fields_own                    = false;
	public $modify_assign_agent_own              = false;
	public $modify_assign_team_own               = false;
	public $modify_assign_self_own               = false;
	public $modify_cc_own                        = false;
	public $modify_merge_own                     = false;
	public $modify_labels_own                    = false;
	public $modify_slas_own                      = false;
	public $modify_notes_own                     = false;
	public $modify_set_hold_own                  = false;
	public $modify_set_awaiting_user_own         = false;
	public $modify_set_awaiting_agent_own        = false;
	public $modify_set_resolved_own              = false;
	public $modify_set_unresolved_own            = false;
	public $modify_messages_own                  = false;
	public $delete_own                           = false;
	public $reply_to_followed                    = false;
	public $modify_followed                      = false;
	public $modify_department_followed           = false;
	public $modify_fields_followed               = false;
	public $modify_assign_agent_followed         = false;
	public $modify_assign_team_followed          = false;
	public $modify_assign_self_followed          = false;
	public $modify_cc_followed                   = false;
	public $modify_merge_followed                = false;
	public $modify_labels_followed               = false;
	public $modify_slas_followed                 = false;
	public $modify_notes_followed                = false;
	public $modify_set_hold_followed             = false;
	public $modify_set_awaiting_user_followed    = false;
	public $modify_set_awaiting_agent_followed   = false;
	public $modify_set_resolved_followed         = false;
	public $modify_set_unresolved_followed       = false;
	public $modify_messages_followed             = false;
	public $delete_followed                      = false;
	public $view_unassigned                      = false;
	public $reply_unassigned                     = false;
	public $modify_unassigned                    = false;
	public $modify_department_unassigned         = false;
	public $modify_fields_unassigned             = false;
	public $modify_assign_agent_unassigned       = false;
	public $modify_assign_team_unassigned        = false;
	public $modify_assign_self_unassigned        = false;
	public $modify_cc_unassigned                 = false;
	public $modify_merge_unassigned              = false;
	public $modify_labels_unassigned             = false;
	public $modify_slas_unassigned               = false;
	public $modify_notes_unassigned              = false;
	public $modify_set_hold_unassigned           = false;
	public $modify_set_awaiting_user_unassigned  = false;
	public $modify_set_awaiting_agent_unassigned = false;
	public $modify_set_resolved_unassigned       = false;
	public $modify_set_unresolved_unassigned     = false;
	public $modify_messages_unassigned           = false;
	public $delete_unassigned                    = false;
	public $view_others                          = false;
	public $reply_others                         = false;
	public $modify_others                        = false;
	public $modify_department_others             = false;
	public $modify_fields_others                 = false;
	public $modify_assign_agent_others           = false;
	public $modify_assign_team_others            = false;
	public $modify_assign_self_others            = false;
	public $modify_cc_others                     = false;
	public $modify_merge_others                  = false;
	public $modify_labels_others                 = false;
	public $modify_slas_others                   = false;
	public $modify_notes_others                  = false;
	public $modify_set_hold_others               = false;
	public $modify_set_awaiting_user_others      = false;
	public $modify_set_awaiting_agent_others     = false;
	public $modify_set_resolved_others           = false;
	public $modify_set_unresolved_others         = false;
	public $modify_messages_others               = false;
	public $delete_others                        = false;
	public $modify_billing                       = false;

	public function getNames()
	{
		return array(
			'use', 'create', 'reply_mass', 'modify_set_closed',
			'reply_own', 'modify_own', 'modify_department_own', 'modify_fields_own', 'modify_assign_agent_own', 'modify_assign_team_own',
			'modify_assign_self_own', 'modify_cc_own', 'modify_merge_own', 'modify_labels_own', 'modify_slas_own', 'modify_notes_own',
			'modify_set_hold_own', 'modify_set_awaiting_user_own', 'modify_set_awaiting_agent_own', 'modify_set_resolved_own', 'modify_set_unresolved_own', 'modify_messages_own', 'delete_own',
			'reply_to_followed', 'modify_followed', 'modify_department_followed', 'modify_fields_followed', 'modify_assign_agent_followed',
			'modify_assign_team_followed', 'modify_assign_self_followed', 'modify_cc_followed', 'modify_merge_followed', 'modify_labels_followed', 'modify_slas_followed',
			'modify_notes_followed', 'modify_set_hold_followed', 'modify_set_awaiting_user_followed', 'modify_set_awaiting_agent_followed',
			'modify_set_resolved_followed', 'modify_set_unresolved_followed', 'modify_messages_followed', 'delete_followed', 'view_unassigned', 'reply_unassigned', 'modify_unassigned',
			'modify_department_unassigned', 'modify_fields_unassigned', 'modify_assign_agent_unassigned', 'modify_assign_team_unassigned',
			'modify_assign_self_unassigned', 'modify_cc_unassigned', 'modify_merge_unassigned', 'modify_labels_unassigned', 'modify_slas_unassigned',
			'modify_notes_unassigned', 'modify_set_hold_unassigned', 'modify_set_awaiting_user_unassigned', 'modify_set_awaiting_agent_unassigned',
			'modify_set_resolved_unassigned', 'modify_set_unresolved_unassigned', 'modify_messages_unassigned', 'delete_unassigned', 'view_others', 'reply_others', 'modify_others',
			'modify_department_others', 'modify_fields_others', 'modify_assign_agent_others', 'modify_assign_team_others', 'modify_assign_self_others',
			'modify_cc_others', 'modify_merge_others', 'modify_labels_others', 'modify_slas_others', 'modify_notes_others', 'modify_set_hold_others',
			'modify_set_awaiting_user_others', 'modify_set_awaiting_agent_others', 'modify_set_resolved_others', 'modify_set_unresolved_others', 'modify_messages_others', 'delete_others', 'modify_billing'
		);
	}

	public function getDestructiveNames()
	{
		return array('delete_own', 'delete_followed', 'delete_unassigned', 'delete_others');
	}
}