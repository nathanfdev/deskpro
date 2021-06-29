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
    /**
     * These are settings considered non-secret. They can be fetched arbitrarily from the API
     * or used in templates with app.getSetting
     *
     * @return string[]
     */
    public static function getAcceptableSettingIds()
    {
        $accept_settings = [
            'agent.disable_notifications',
            'agent.login_lockout_time',
            'agent.max_login_attempts',
            'agent.notify_login_emaillist',
            'agent.notify_self_login',
            'agent.password_policy.forbid_reuse',
            'agent.password_policy.min_length',
            'agent.password_policy.require_num_lowercase',
            'agent.password_policy.require_num_number',
            'agent.password_policy.require_num_symbol',
            'agent.password_policy.require_num_uppercase',
            'agent.ui_snippets_use_client_db',
            'agent_notify_list_adminlogin',
            'agent_notify_list_failed_adminlogin',
            'agent_notify_list_failed_login',
            'agent_notify_list_login',
            'api_limits.global.day',
            'api_limits.global.hour',
            'beta_features.voice',
            'core.admin_upgrade_notice',
            'core.agent_enable_kb_shortcuts',
            'core.agent_translate_debug',
            'core.allow_arbitrary_gateway_address',
            'core.apps_jira.defaultProject',
            'core.apps_jira.defaultTags',
            'core.apps_tasks',
            'core.attach_user_maxsize',
            'core.bcc_all_emails',
            'core.date_day',
            'core.default_country_code',
            'core.default_prod_id',
            'core.default_ticket_cat',
            'core.default_ticket_pri',
            'core.default_ticket_work',
            'core.default_timezone',
            'core.deskpro_build',
            'core.deskpro_name',
            'core.deskpro_url',
            'core.dev_use_rjs_build',
            'core.disable_rjs_build',
            'core.drafts_lifetime',
            'core.email_source_storetime',
            'core.enable_user_rememberme',
            'core.favicon_blob_id',
            'core.iface_portal',
            'core.install_key',
            'core.install_source',
            'core.install_timestamp',
            'core.install_token',
            'core.lang_auto_detect',
            'core.lang_auto_install',
            'core.problems.enabled',
            'core.sessions_lifetime',
            'core.show_old_reports',
            'core.show_share_facebook',
            'core.show_share_linkedin',
            'core.show_share_twitter',
            'core.show_share_whatsapp',
            'core.show_share_widget',
            'core.site_id',
            'core.site_name',
            'core.site_url',
            'core.store_sent_mail_days',
            'core.use_agent_team',
            'core.use_gravatar',
            'core.use_product',
            'core.use_product_fields',
            'core.use_ticket_category',
            'core.use_ticket_priority',
            'core.use_ticket_workflow',
            'core_chat.agent_timeout',
            'core_chat.assign_ack_timeout',
            'core_chat.require_department',
            'core_chat.user_timeout',
            'core_email.antiflood_newreplies',
            'core_email.antiflood_newreplies_warn',
            'core_email.antiflood_newtickets',
            'core_email.antiflood_newtickets_warn',
            'core_email.enable_date_limit_rejection',
            'core_email.failed_email_attempts_notify',
            'core_email.google_oauth_client_id',
            'core_email.google_oauth_secret',
            'core_misc.cleanup_gateway_sources',
            'core_misc.cleanup_gateway_sources_onlyclosed',
            'core_misc.cleanup_login_logs',
            'core_misc.cleanup_task_logs',
            'core_tickets.agent_rte_button_alignment',
            'core_tickets.agent_rte_button_bold',
            'core_tickets.agent_rte_button_color',
            'core_tickets.agent_rte_button_hr',
            'core_tickets.agent_rte_button_html',
            'core_tickets.agent_rte_button_image',
            'core_tickets.agent_rte_button_italic',
            'core_tickets.agent_rte_button_link',
            'core_tickets.agent_rte_button_list',
            'core_tickets.agent_rte_button_strike',
            'core_tickets.agent_rte_button_table',
            'core_tickets.agent_rte_button_underline',
            'core_tickets.billing_auto_timer',
            'core_tickets.billing_auto_timer_new',
            'core_tickets.billing_currency',
            'core_tickets.billing_on_reply',
            'core_tickets.default_send_user_notify',
            'core_tickets.disable_agent_notifications',
            'core_tickets.disable_attachments_list',
            'core_tickets.disable_pending',
            'core_tickets.email_cc_max_count',
            'core_tickets.email_reply_as_note',
            'core_tickets.enable_agent_rte',
            'core_tickets.enable_billing',
            'core_tickets.enable_email_preview',
            'core_tickets.enable_feedback',
            'core_tickets.enable_like_search_mode',
            'core_tickets.enable_timelog',
            'core_tickets.feedback_agents_read',
            'core_tickets.forward_as_new_linked_ticket',
            'core_tickets.fwd_use_agent_address',
            'core_tickets.gateway_agent_require_marker',
            'core_tickets.gateway_enable_subject_match',
            'core_tickets.lock_on_view',
            'core_tickets.new_assign',
            'core_tickets.new_assignteam',
            'core_tickets.new_default_send_user_notify',
            'core_tickets.new_status',
            'core_tickets.newticket_enable_drafts',
            'core_tickets.pending_status_waiting_time_mode',
            'core_tickets.reassign_auto_change_status',
            'core_tickets.reply_assign_assigned',
            'core_tickets.reply_assign_unassigned',
            'core_tickets.reply_assignteam_assigned',
            'core_tickets.reply_assignteam_unassigned',
            'core_tickets.reply_status',
            'core_tickets.resolve_auto_close_tab',
            'core_tickets.ticket_approvals',
            'core_tickets.unlock_on_close',
            'core_tickets.use_archive',
            'core_tickets.use_ref',
            'custom_cloud_billing_authcode',
            'DESKPRO_APP_ASSETS_URL',
            'disable_admin_deskpro_updates',
            'disable_server_section',
            'experimental_admin_features',
            'internal.disable_email_editing.incoming_details',
            'internal.disable_email_editing.new',
            'portal.members_community',
            'portal.profile_directory_fields',
            'rdns_ticket_messages',
            'services.language_sync_api',
            'user.community_subscriptions',
            'user.downloads_subscriptions',
            'user.kb_categories_with_tree',
            'user.kb_subscriptions',
            'user.news_subscriptions',
            'user.non_published_articles_on_helpcenter',
            'user.portal_enabled',
            'user.publish_comments',
            'using_department',
            'voice.email_attach_recording',
            'voice.email_attach_transcription',
            'voice.transcribe_voicemail',

            'messenger.widget.lang_version',
            'messenger.widget.primary_color',
            'messenger.widget.bg_color',
            'messenger.widget.text_color',
            'messenger.widget.position',
            'messenger.widget.icon',
            'messenger.chat.enabled',
            'messenger.chat.department',
            'messenger.chat.usergroups',
            'messenger.chat.timeout',
            'messenger.chat.no_answer',
            'messenger.chat.options.show_photos',
            'messenger.chat.ticket_defaults.subject',
            'messenger.chat.ticket_defaults.subject_type',
            'messenger.chat.ticket_defaults.department',
            'messenger.chat.pre_chat_form.enabled',
            'messenger.chat.pre_chat_form.name.enabled',
            'messenger.chat.pre_chat_form.email.enabled',
            'messenger.chat.pre_chat_form.name.required',
            'messenger.chat.pre_chat_form.email.required',
            'messenger.chat.pre_chat_form.department',
            'messenger.chat.pre_chat_form.fields',
            'messenger.chat.pre_chat_form.form_message_enabled',
            'messenger.proactive.autostart',
            'messenger.proactive.autostart_timeout',
            'messenger.proactive.autostart_style',
            'messenger.tickets.enabled',
            'messenger.tickets.subject',
            'messenger.tickets.department',
            'messenger.tickets.department_option',
            'messenger.tickets.subject_option',
            'messenger.embed.show_on_portal',
            'messenger.embed.authorize_domains',
        ];

        if (!defined('DPC_IS_CLOUD')) {
            $accept_settings[] = 'api_limits.global.hour';
            $accept_settings[] = 'api_limits.global.day';
        }

        return $accept_settings;
    }

    public function getShowSettings()
    {
        $settings = parent::getAllSettings();

        $accept_settings = self::getAcceptableSettingIds();

        $ret = [];

        foreach ($accept_settings as $k) {
            if (isset($settings[$k])) {
                $ret[$k] = $settings[$k];
            }
        }

        return $ret;
    }
}
