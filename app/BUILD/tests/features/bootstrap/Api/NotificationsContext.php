<?php

namespace DpBehat\Api;

use DpBehat\BaseContext;

/**
 * Class NotificationsContext.
 */
class NotificationsContext extends BaseContext
{
    /**
     * @var bool
     */
    private static $prepared = false;

    /**
     * @Given I prepare notifications data
     */
    public function iPrepareNotificationsData()
    {
        if (self::$prepared) {
            return;
        }

        $this->em()->getConnection()->executeQuery(<<<'SQL'
INSERT INTO `people` (`id`, `organization_id`, `primary_email_id`, `gravatar_url`, `disable_picture`, `is_contact`, `is_user`, `is_agent`, `was_agent`, `can_agent`, `can_admin`, `can_billing`, `can_reports`, `is_vacation_mode`, `disable_autoresponses`, `disable_autoresponses_log`, `is_confirmed`, `is_deleted`, `is_disabled`, `importance`, `creation_system`, `name`, `first_name`, `last_name`, `title_prefix`, `override_display_name`, `summary`, `secret_string`, `organization_position`, `organization_manager`, `timezone`, `password`, `password_scheme`, `salt`, `date_created`, `date_password_set`) VALUES ('528', null, null, '', '0', '1', '1', '1', '0', '1', '1', '1', '1', '0', '0', '', '1', '0', '0', '0', 'web.person', 'Link Admin', 'Link', 'Admin', '', '', '', '9SyAoYxX9qswxReKD4ySa0TaWINqnrF44cr5ZSYm', '', '0', 'UTC', '', 'bcrypt', '', '2016-02-17 14:05:25', '2016-02-17 14:05:25');
INSERT INTO `people` (`id`, `organization_id`, `primary_email_id`, `gravatar_url`, `disable_picture`, `is_contact`, `is_user`, `is_agent`, `was_agent`, `can_agent`, `can_admin`, `can_billing`, `can_reports`, `is_vacation_mode`, `disable_autoresponses`, `disable_autoresponses_log`, `is_confirmed`, `is_deleted`, `is_disabled`, `importance`, `creation_system`, `name`, `first_name`, `last_name`, `title_prefix`, `override_display_name`, `summary`, `secret_string`, `organization_position`, `organization_manager`, `timezone`, `password`, `password_scheme`, `salt`, `date_created`, `date_password_set`) VALUES ('529', null, null, '', '0', '1', '1', '1', '0', '1', '0', '0', '0', '0', '0', '', '1', '0', '0', '0', 'web.person', 'Zelda Agent', 'Zelda', 'Agent', '', '', '', 'DNAhz34xc623ipBUnpEnlvcJo3yu3hxrGDNacjZp', '', '0', 'UTC', '', 'bcrypt', '', '2016-02-17 14:05:25', '2016-02-17 14:05:25');
INSERT INTO `people` (`id`, `organization_id`, `primary_email_id`, `gravatar_url`, `disable_picture`, `is_contact`, `is_user`, `is_agent`, `was_agent`, `can_agent`, `can_admin`, `can_billing`, `can_reports`, `is_vacation_mode`, `disable_autoresponses`, `disable_autoresponses_log`, `is_confirmed`, `is_deleted`, `is_disabled`, `importance`, `creation_system`, `name`, `first_name`, `last_name`, `title_prefix`, `override_display_name`, `summary`, `secret_string`, `organization_position`, `organization_manager`, `timezone`, `password`, `password_scheme`, `salt`, `date_created`, `date_password_set`) VALUES ('530', null, null, '', '0', '1', '1', '0', '0', '0', '0', '0', '0', '0', '0', '', '1', '0', '0', '0', 'web.person', 'Ganon User', 'Ganon', 'User', '', '', '', 'uda282CGCRIdoX3jbCotBXkxeovIqCQmwAOaRBtW', '', '0', 'UTC', '', 'bcrypt', '', '2016-02-17 14:05:25', '2016-02-17 14:05:25');

SQL
        );

        $this->em()->getConnection()->executeQuery(<<<'SQL'
INSERT INTO `tickets` (`id`, `department_id`, `person_id`, `agent_id`, `ref`, `auth`, `sent_to_address`, `email_account_address`, `creation_system`, `creation_system_option`, `ticket_hash`, `status`, `is_hold`, `urgency`, `count_agent_replies`, `count_user_replies`, `date_created`, `date_first_agent_assign`, `date_status`, `total_user_waiting`, `total_to_first_reply`, `has_attachments`, `subject`, `original_subject`) VALUES ('567', '1', '3', '1', 'ref-567', 'S9MQ62GDGS57529', '', '', 'unknown', '', 'none', 'awaiting_agent', '0', '1', '0', '0', '2016-02-17 14:05:25', '2016-02-17 14:05:25', '2016-02-17 14:05:25', '0', '0', '0', 'Ticket #567', 'Ticket #567');
INSERT INTO `tickets` (`id`, `department_id`, `person_id`, `agent_id`, `ref`, `auth`, `sent_to_address`, `email_account_address`, `creation_system`, `creation_system_option`, `ticket_hash`, `status`, `is_hold`, `urgency`, `count_agent_replies`, `count_user_replies`, `date_created`, `date_first_agent_assign`, `date_status`, `total_user_waiting`, `total_to_first_reply`, `has_attachments`, `subject`, `original_subject`) VALUES ('568', '1', '3', '2', 'ref-568', 'SQ7M3HNY2RNW2NB', '', '', 'unknown', '', 'none', 'awaiting_agent', '0', '1', '0', '0', '2016-02-17 14:05:25', '2016-02-17 14:05:25', '2016-02-17 14:05:25', '0', '0', '0', 'Ticket #568', 'Ticket #568');
INSERT INTO `tickets` (`id`, `department_id`, `person_id`, `agent_id`, `ref`, `auth`, `sent_to_address`, `email_account_address`, `creation_system`, `creation_system_option`, `ticket_hash`, `status`, `is_hold`, `urgency`, `count_agent_replies`, `count_user_replies`, `date_created`, `date_first_agent_assign`, `date_status`, `total_user_waiting`, `total_to_first_reply`, `has_attachments`, `subject`, `original_subject`) VALUES ('569', '2', '3', '1', 'ref-569', 'K5HCS46QZY47XN9', '', '', 'unknown', '', 'none', 'awaiting_agent', '0', '1', '0', '0', '2016-02-17 14:05:25', '2016-02-17 14:05:25', '2016-02-17 14:05:25', '0', '0', '0', 'Ticket #569', 'Ticket #569');

SQL
        );

        self::$prepared = true;
    }
}
