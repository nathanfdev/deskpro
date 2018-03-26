<?php

/**
 * DeskPRO.
 *
 * @category Controller
 */

namespace Application\DeskPRO\ResourceScanner;

/**
 * Defines which settings are to be displayed in the 'advanced' page.
 */
class AdvancedSettings extends SettingFiles
{
    public function getShowSettings()
    {
        $settings = parent::getAllSettings();

        $accept_settings = [
            'agent.max_login_attempts',
            'agent.login_lockout_time',
            'agent.notify_self_login',
            'agent.notify_login_emaillist',
            'agent_notify_list_login',
            'agent_notify_list_failed_login',
            'agent_notify_list_adminlogin',
            'agent_notify_list_failed_adminlogin',
            'core_chat.assign_ack_timeout',
            'core_chat.agent_timeout',
            'core_chat.user_timeout',
            'core_chat.require_department',
            'core.bcc_all_emails',
            'core.drafts_lifetime',
            'core.store_sent_mail_days',
            'core.site_id',
            'core.sessions_lifetime',
            'core.email_source_storetime',
            'core_email.failed_email_attempts_notify',
            'core_email.antiflood_newtickets',
            'core_email.antiflood_newtickets_warn',
            'core_email.antiflood_newreplies',
            'core_email.antiflood_newreplies_warn',
            'core_misc.cleanup_login_logs',
            'core_misc.cleanup_gateway_sources',
            'core_misc.cleanup_gateway_sources_onlyclosed',
            'core_misc.cleanup_task_logs',
            'core.allow_arbitrary_gateway_address',
            'core_tickets.gateway_agent_require_marker',
            'core_tickets.gateway_enable_subject_match',
            'core.agent_translate_debug',
            'core_email.enable_date_limit_rejection',
        ];

        if (!defined('DPC_IS_CLOUD')) {
            $accept_settings[] = 'core.api_rate_limit';
        }

        $ret = [];

        foreach ($accept_settings as $k) {
            if (isset($settings[$k])) {
                $ret[$k] = $settings[$k];
            }
        }

        return $ret;
    }
}
