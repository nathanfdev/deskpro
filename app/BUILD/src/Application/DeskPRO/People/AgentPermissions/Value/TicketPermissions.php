<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\AgentPermissions\Value;

class TicketPermissions implements PermissionValueInterface
{
    /** @var bool */
    public $use = false;
    /** @var bool */
    public $create = false;
    /** @var bool */
    public $reply_mass = false;
    /** @var bool */
    public $modify_set_archived = false;
    /** @var bool */
    public $reply_own = false;
    /** @var bool */
    public $modify_own = false;
    /** @var bool */
    public $modify_department_own = false;
    /** @var bool */
    public $modify_fields_own = false;
    /** @var bool */
    public $modify_assign_agent_own = false;
    /** @var bool */
    public $modify_assign_team_own = false;
    /** @var bool */
    public $modify_assign_self_own = false;
    /** @var bool */
    public $modify_cc_own = false;
    /** @var bool */
    public $modify_merge_own = false;
    /** @var bool */
    public $modify_labels_own = false;
    /** @var bool */
    public $modify_slas_own = false;
    /** @var bool */
    public $modify_notes_own = false;
    /** @var bool */
    public $modify_set_hold_own = false;
    /** @var bool */
    public $modify_set_awaiting_user_own = false;
    /** @var bool */
    public $modify_set_awaiting_agent_own = false;
    /** @var bool */
    public $modify_set_resolved_own = false;
    /** @var bool */
    public $modify_set_unresolved_own = false;
    /** @var bool */
    public $modify_messages_own = false;
    /** @var bool */
    public $modify_billing_own = false;
    /** @var bool */
    public $delete_own = false;
    /** @var bool */
    public $reply_to_followed = false;
    /** @var bool */
    public $modify_followed = false;
    /** @var bool */
    public $modify_department_followed = false;
    /** @var bool */
    public $modify_fields_followed = false;
    /** @var bool */
    public $modify_assign_agent_followed = false;
    /** @var bool */
    public $modify_assign_team_followed = false;
    /** @var bool */
    public $modify_assign_self_followed = false;
    /** @var bool */
    public $modify_cc_followed = false;
    /** @var bool */
    public $modify_merge_followed = false;
    /** @var bool */
    public $modify_labels_followed = false;
    /** @var bool */
    public $modify_slas_followed = false;
    /** @var bool */
    public $modify_notes_followed = false;
    /** @var bool */
    public $modify_set_hold_followed = false;
    /** @var bool */
    public $modify_set_awaiting_user_followed = false;
    /** @var bool */
    public $modify_set_awaiting_agent_followed = false;
    /** @var bool */
    public $modify_set_resolved_followed = false;
    /** @var bool */
    public $modify_set_unresolved_followed = false;
    /** @var bool */
    public $modify_messages_followed = false;
    /** @var bool */
    public $modify_billing_followed = false;
    /** @var bool */
    public $delete_followed = false;
    /** @var bool */
    public $view_unassigned = false;
    /** @var bool */
    public $reply_unassigned = false;
    /** @var bool */
    public $modify_unassigned = false;
    /** @var bool */
    public $modify_department_unassigned = false;
    /** @var bool */
    public $modify_fields_unassigned = false;
    /** @var bool */
    public $modify_assign_agent_unassigned = false;
    /** @var bool */
    public $modify_assign_team_unassigned = false;
    /** @var bool */
    public $modify_assign_self_unassigned = false;
    /** @var bool */
    public $modify_cc_unassigned = false;
    /** @var bool */
    public $modify_merge_unassigned = false;
    /** @var bool */
    public $modify_labels_unassigned = false;
    /** @var bool */
    public $modify_slas_unassigned = false;
    /** @var bool */
    public $modify_notes_unassigned = false;
    /** @var bool */
    public $modify_set_hold_unassigned = false;
    /** @var bool */
    public $modify_set_awaiting_user_unassigned = false;
    /** @var bool */
    public $modify_set_awaiting_agent_unassigned = false;
    /** @var bool */
    public $modify_set_resolved_unassigned = false;
    /** @var bool */
    public $modify_set_unresolved_unassigned = false;
    /** @var bool */
    public $modify_messages_unassigned = false;
    /** @var bool */
    public $modify_billing_unassigned = false;
    /** @var bool */
    public $delete_unassigned = false;
    /** @var bool */
    public $view_others = false;
    /** @var bool */
    public $reply_others = false;
    /** @var bool */
    public $modify_others = false;
    /** @var bool */
    public $modify_department_others = false;
    /** @var bool */
    public $modify_fields_others = false;
    /** @var bool */
    public $modify_assign_agent_others = false;
    /** @var bool */
    public $modify_assign_team_others = false;
    /** @var bool */
    public $modify_assign_self_others = false;
    /** @var bool */
    public $modify_cc_others = false;
    /** @var bool */
    public $modify_merge_others = false;
    /** @var bool */
    public $modify_labels_others = false;
    /** @var bool */
    public $modify_slas_others = false;
    /** @var bool */
    public $modify_notes_others = false;
    /** @var bool */
    public $modify_set_hold_others = false;
    /** @var bool */
    public $modify_set_awaiting_user_others = false;
    /** @var bool */
    public $modify_set_awaiting_agent_others = false;
    /** @var bool */
    public $modify_set_resolved_others = false;
    /** @var bool */
    public $modify_set_unresolved_others = false;
    /** @var bool */
    public $modify_messages_others = false;
    /** @var bool */
    public $delete_others = false;
    /** @var bool */
    public $modify_billing_others = false;
    /** @var bool */
    public $create_labels = false;

    /** @var bool */
    public $associate_problem_own = false;
    /** @var bool */
    public $disassociate_problem_own = false;
    /** @var bool */
    public $associate_problem_followed = false;
    /** @var bool */
    public $disassociate_problem_followed = false;
    /** @var bool */
    public $associate_problem_unassigned = false;
    /** @var bool */
    public $disassociate_problem_unassigned = false;
    /** @var bool */
    public $associate_problem_others = false;
    /** @var bool */
    public $disassociate_problem_others = false;

    public function getNames()
    {
        return [
            'use', 'create', 'reply_mass', 'modify_set_archived',
            'reply_own', 'modify_own', 'modify_department_own', 'modify_fields_own', 'modify_assign_agent_own', 'modify_assign_team_own',
            'modify_assign_self_own', 'modify_cc_own', 'modify_merge_own', 'modify_labels_own', 'modify_slas_own', 'modify_notes_own',
            'modify_set_hold_own', 'modify_set_awaiting_user_own', 'modify_set_awaiting_agent_own', 'modify_set_resolved_own', 'modify_set_unresolved_own', 'modify_messages_own', 'modify_billing_own', 'delete_own',
            'reply_to_followed', 'modify_followed', 'modify_department_followed', 'modify_fields_followed', 'modify_assign_agent_followed',
            'modify_assign_team_followed', 'modify_assign_self_followed', 'modify_cc_followed', 'modify_merge_followed', 'modify_labels_followed', 'modify_slas_followed',
            'modify_notes_followed', 'modify_set_hold_followed', 'modify_set_awaiting_user_followed', 'modify_set_awaiting_agent_followed',
            'modify_set_resolved_followed', 'modify_set_unresolved_followed', 'modify_messages_followed', 'modify_billing_followed', 'delete_followed', 'view_unassigned', 'reply_unassigned', 'modify_unassigned',
            'modify_department_unassigned', 'modify_fields_unassigned', 'modify_assign_agent_unassigned', 'modify_assign_team_unassigned',
            'modify_assign_self_unassigned', 'modify_cc_unassigned', 'modify_merge_unassigned', 'modify_labels_unassigned', 'modify_slas_unassigned',
            'modify_notes_unassigned', 'modify_set_hold_unassigned', 'modify_set_awaiting_user_unassigned', 'modify_set_awaiting_agent_unassigned',
            'modify_set_resolved_unassigned', 'modify_set_unresolved_unassigned', 'modify_messages_unassigned', 'modify_billing_unassigned', 'delete_unassigned', 'view_others', 'reply_others', 'modify_others',
            'modify_department_others', 'modify_fields_others', 'modify_assign_agent_others', 'modify_assign_team_others', 'modify_assign_self_others',
            'modify_cc_others', 'modify_merge_others', 'modify_labels_others', 'modify_slas_others', 'modify_notes_others', 'modify_set_hold_others',
            'modify_set_awaiting_user_others', 'modify_set_awaiting_agent_others', 'modify_set_resolved_others', 'modify_set_unresolved_others', 'modify_messages_others', 'delete_others', 'modify_billing_others',
            'create_labels', 'associate_problem_own', 'associate_problem_followed', 'associate_problem_unassigned', 'associate_problem_others',
            'disassociate_problem_own', 'disassociate_problem_followed', 'disassociate_problem_unassigned', 'disassociate_problem_others',
        ];
    }

    public function getDestructiveNames()
    {
        return ['delete_own', 'delete_followed', 'delete_unassigned', 'delete_others'];
    }
}
