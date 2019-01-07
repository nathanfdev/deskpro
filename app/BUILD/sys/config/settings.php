<?php

return [

    //###################################################################################################################
    // core
    //###################################################################################################################

    /*
     * Is multi-language features enabled?
     */
    'core.enable_languages' => true,

    /**#@+
     * Date formats
     */
    'core.date_fulltime'  => 'D, jS M Y g:ia',
    'core.date_full'      => 'D, jS M Y',
    'core.date_day'       => 'M j Y',
    'core.date_day_short' => 'M j',
    'core.date_time'      => 'g:i a',
    /**#@-*/

    /*
     * Are ticket categories enabled?
     */
    'core.use_ticket_category' => false,

    /*
     * Are ticket priorities enabled?
     */
    'core.use_ticket_priority' => false,

    /*
     * Are ticket workflows enabled?
     */
    'core.use_ticket_workflow' => false,

    /*
     * Are agent teams enabled?
     */
    'core.use_agent_team' => true,

    /*
     * Are products enabled?
     */
    'core.use_product' => false,

    /*
     * Are custom fields on products enabled?
     */
    'core.use_product_fields' => false,

    /*
     * Is the helpdesk disabled? A disabled helpdesk doesn't fully boot and shows a maintenance message.
     */
    'core.helpdesk_disabled' => false,

    /*
     * The default message to show when helpdesk is disabled
     * Note that this is actually written to a file when updated, this record is kept just
     * as the default.
     */
    'core.helpdesk_disabled_message' => 'Our helpdesk is temporarily offline for maintenance. We will be back up in about 10 minutes.',

    /*
     * The URL to the DeskPRO installation.
     * Should include trailing slash. Should NOT include index.php portion.
     */
    'core.deskpro_url' => '',

    /*
     * Auto-correct the URL?
     */
    'core.deskpro_url_autocorrect' => true,

    /*
     * The name of the DeskPRO helpdesk
     */
    'core.deskpro_name' => 'DeskPRO',

    /*
     * The optional tagline or sub-title of the helpdesk
     */
    'core.deskpro_tagline' => '',

    /*
     * The blobid of the uploaded logo image
     */
    'core.deskpro_logo_blob' => 0,

    /*
     * The optional URL to a main website
     */
    'core.site_url' => '',

    /*
     * The optional title for the site URL
     */
    'core.site_name' => '',

    /*
     * The secret string used for this installation used when generating various hashes
     */
    'core.app_secret' => 'APP_SECRET',

    /*
     * A UUID string to identify this helpdesk instance. It's set up during install.
     */
    'core.helpdesk_uuid' => 'HELPDESK_UUID',

    /*
     * The default "from" address to send all email from
     */
    'core.default_from_email' => '',

    /*
     * Optionally BCC all emails to this address
     */
    'core.bcc_all_emails' => '',

    /*
     * How long, in days, to save successfully sent emails to
     */
    'core.store_sent_mail_days' => 0,

    /*
     * When the installation took place
     */
    'core.install_time' => 0,

    /*
     * The site ID. Only used when multiple helpdesks need to talk to eachother.
     */
    'core.site_id' => '1',

    /*
     * When enabled, error reports and heartbeats dont send server stats,
     * and heartbeats dont send db stats
     */
    'core.enable_reduced_lic_reports' => false,

    /*
     * The cookie path
     */
    'core.cookie_path' => '/',

    /*
     * The cookie domains
     */
    'core.cookie_domain' => '',

    /*
     * The language to use by default when a user has no selection
     */
    'core.default_language_id' => 1,

    /*
     * The timezone to use by default when a user has no selection
     */
    'core.default_timezone' => 'UTC',

    /*
     * The default 2-letter country code (ISO 3166-1 alpha-2 format)
     */
    'core.default_country_code' => 'US',

    /*
     * Use gravatar for default avatars?
     */
    'core.use_gravatar' => 0,

    /*
     * Enable remember-me for agents?
     */
    'core.enable_agent_rememberme' => true,

    /*
     * Enable remember-me for users?
     */
    'core.enable_user_rememberme' => true,

    /*
     * How long, in seconds, are sessions valid for?
     */
    'core.sessions_lifetime' => 3600,

    /*
     * Require an actual pageload to keep a session alive (e.g., automatic pings dont keep sessions alive)
     */
    'core.session_keepalive_require_page' => false,

    /*
     * How long, in seconds, are email sources kept for before being cleaned up
     */
    'core.email_source_storetime'           => 5184000, // 60 days
    'core.email_source_storetime_error'     => 15552000, // 180 days
    'core.email_source_storetime_rejection' => 1296000, // 15 days

    /*
     * An email address to email whenever there is a rejection
     */
    'core.email_source_alert_rejection' => '',

    /*
     * How long to store sendmail sources
     */
    'core.sendmail_source_storetime'       => 1728000, // 20 days
    'core.sendmail_source_storetime_error' => 3456000, // 40 days

    /*
     * Adapter to store log files under.
     */
    'core.filestorage_method_logs' => 'db',

    /*
     * How long to store ticket manager logs for
     */
    'core.ticket_manager_log_storetime' => 604800, // 7 days

    //'core.disqus_shortname' => '',
    //'core.facebook_comments_num_posts' => 10,
    //'core.facebook_admins' => '',
    //'core.facebook_like' => false,

    /*
     * Which comment adapter to use: false=deskpro, disqus or facebook
     */
    'core.comments_adapter' => false,

    /*
     * Recaptcha public key
     */
    'core.recaptcha_public_key' => '6LcWL8YSAAAAAJu1CrtS9RdOJyKd_NbArNgUFWV9',

    /*
     * Recaptcha private key
     */
    'core.recaptcha_private_key' => '6LcWL8YSAAAAAJVZ5AEMb5Vq7wkZoGHfKfAqvB2U',

    /*
     * Default storage method for blobs is the db. Other values: fs
     */
    'core.filestorage_method' => 'db',

    /*
     * The max attachment an agent can upload
     */
    'core.attach_agent_maxsize' => 1024 * 1024 * 25,

    /*
     * The only file extensions that an agent can upload (whitelist)
     */
    'core.attach_agent_must_exts' => null,

    /*
     * File extensions that an agent is forbidden from uploading (blacklist)
     */
    'core.attach_agent_not_exts' => null,

    /*
     * The max attachment a user can upload
     */
    'core.attach_user_maxsize' => 1024 * 1024 * 10,

    /*
     * The only file extensions that users can upload (whitelist)
     */
    'core.attach_user_must_exts' => null,

    /*
     * File extensions that a user is forbidden from uploading (blacklist)
     */
    'core.attach_user_not_exts' => null,

    'core.emails.attach_agent_maxsize'   => '26214400',
    'core.emails.attach_agent_must_exts' => null,
    'core.emails.attach_agent_not_exts'  => null,
    'core.emails.attach_user_maxsize'    => '26214400',
    'core.emails.attach_user_must_exts'  => null,
    'core.emails.attach_user_not_exts'   => null,

    /*
     * Download Hotlinked Images in incoming emails and replace them  by blob attachment
     */
    'core.emails.download_hotlinked_images.enabled'       => true,
    'core.emails.download_hotlinked_images.image_maxsize' => 10 * 1024 * 1024,
    'core.emails.download_hotlinked_images.total_maxsize' => 25 * 1024 * 1024,

    /*
     * True to have the DeskPRO local user source enabled
     */
    'core.deskpro_source_enabled' => true,

    /*
     * True if we always replace local name (fname, lname, name) and other data with usersource data on every login.
     * False means we only do this on first login with that usersource.
     */
    'core.usersource_always_update_data' => true,

    /*
     * True to have links from chat intercepted and sent through the security page
     */
    'core.agent_intercept_external_link' => false,

    /*
     * Show the share widget (twitter/facebook/linkedin/gplus)
     */
    'core.show_share_widget'   => false,
    'core.show_share_facebook' => false,
    'core.show_share_twitter'  => false,
    'core.show_share_linkedin' => false,
    'core.show_share_gplus'    => false,

    /*
     * Enable the KB?
     */
    'core.apps_kb' => 1,

    /*
     * Enable feedback?
     */
    'core.apps_feedback' => 1,

    /*
     * Enable agent tasks?
     */
    'core.apps_tasks' => 1,

    /*
     * Enable news
     */
    'core.apps_news' => 1,

    /*
     * Enable downloads
     */
    'core.apps_downloads' => 1,

    'core.iface_portal' => true,
    'core.iface_widget' => true,

    /*
     * Maximum SMS chunks to send for a single message
     */
    'core.max_sms_chunks' => 5,

    /*
     * Max email size to read from gateways
     */
    'core.gateway_max_email' => 41943040,

    /*
     * True to disable floodchecking in gateway
     */
    'core.disable_gateway_floodcheck' => false,

    /*
     * Show suggestions newticket
     */
    'core.show_ticket_suggestions' => true,

    /*
     * Max size of attachments to send in email notifications
     */
    'core.sendemail_attach_maxsize' => 7340032,

    /*
     * Automatically install new languages on upgrade
     */
    'core.lang_auto_install' => false,

    /*
     * How long, in seconds, are drafts valid for?
     */
    'core.drafts_lifetime' => 604800, // 1 week

    /*
     * A Google Analytics property ID (UA-XXXXX-Y). If added, GA will be enabled on user pages.
     */
    'core.ga_property_id' => '',

    /*
     * Interval to limit allowed requests to API in seconds
     */
    'core.api_rate_limit_interval' => 60,
    /*
     * Number of allowed requests to the API per interval. 0 to disable.
     */
    'core.api_rate_limit' => defined('DPC_IS_CLOUD') ? 80 : 0,

    'core.twitter_agent_consumer_key'    => '',
    'core.twitter_agent_consumer_secret' => '',
    'core.twitter_user_consumer_key'     => '',
    'core.twitter_user_consumer_secret'  => '',
    'core.twitter_auto_remove_time'      => 1209600,

    /*
     * Time in H:i format for when to send task notifs.
     * The time is considered to be of the default timezone.
     */
    'core.task_reminder_time' => '09:00',

    /*
     * How many visitor tracks to keep around at a time
     * when storing in the db
     */
    'core.hit_tracks_db_count' => 7500,

    'core.agent_translate_debug'     => false,
    'core.agent_enable_kb_shortcuts' => true,

    //###################################################################################################################
    // core_tickets
    //###################################################################################################################

    'core_tickets.lock_timeout'     => 600,
    'core_tickets.hard_delete_time' => 2419000,
    'core_tickets.spam_delete_time' => 2419000,

    'core_tickets.enable_feedback'      => 1,
    'core_tickets.feedback_agents_read' => 1,

    'core_tickets.enable_timelog'         => 0,
    'core_tickets.enable_billing'         => 0,
    'core_tickets.billing_on_reply'       => 0,
    'core_tickets.billing_auto_timer'     => 0,
    'core_tickets.billing_on_new'         => 0,
    'core_tickets.billing_auto_timer_new' => 0,
    'core_tickets.billing_currency'       => 'USD',

    'core_tickets.enable_agent_rte'           => true,
    'core_tickets.agent_rte_button_html'      => false,
    'core_tickets.agent_rte_button_bold'      => true,
    'core_tickets.agent_rte_button_italic'    => true,
    'core_tickets.agent_rte_button_underline' => true,
    'core_tickets.agent_rte_button_strike'    => false,
    'core_tickets.agent_rte_button_color'     => true,
    'core_tickets.agent_rte_button_list'      => true,
    'core_tickets.agent_rte_button_image'     => true,
    'core_tickets.agent_rte_button_link'      => true,
    'core_tickets.agent_rte_button_table'     => false,
    'core_tickets.agent_rte_button_hr'        => true,
    'core_tickets.agent_rte_button_alignment' => false,

    'core_tickets.web_require_validation'   => false,
    'core_tickets.email_require_validation' => false,
    'core_tickets.attachment_require_auth'  => false,
    'core_tickets.enable_email_preview'     => true,
    'core_tickets.use_ref'                  => false,

    'core_tickets.email_history_limit' => 11, // 10 + 1 for the original message at top

    'core.allow_arbitrary_gateway_address' => 1,

    'core_tickets.use_archive'       => 1,
    'core_tickets.auto_archive_time' => 31536000,

    'core_tickets.enable_like_search_mode' => true,

    // See TicketMessage::checkDupeMessage
    'core_tickets.enable_dupe_checking' => true,

    // See TicketGatewayProcessor::createTicketDetector
    'core_tickets.gateway_enable_subject_match' => true,

    // See TicketGatewayProcessor::createTicketDetector and SubjectMatchDetector::enableSameAccountMatching
    'core_tickets.enable_same_account_subject_matching' => false,

    // See TicketGatewayProcessor::createTicketDetector and SubjectMatchDetector::enableExactSubjectMatching
    'core_tickets.enable_exact_subject_matching' => false,

    'core_tickets.core_tickets_disable_attachments_list' => false,

    // True to force agent emails to have the marker line
    'core_tickets.gateway_agent_require_marker' => true,

    'core_tickets.reply_status'                 => 'awaiting_user',
    'core_tickets.reply_assign_unassigned'      => 'assign',
    'core_tickets.reply_assign_assigned'        => false,
    'core_tickets.reply_assignteam_unassigned'  => false,
    'core_tickets.reply_assignteam_assigned'    => false,
    'core_tickets.reassign_auto_change_status'  => false,
    'core_tickets.resolve_auto_close_tab'       => false,
    'core_tickets.new_status'                   => 'awaiting_user',
    'core_tickets.new_assign'                   => 'assign',
    'core_tickets.new_assignteam'               => false,
    'core_tickets.default_send_user_notify'     => true,
    'core_tickets.new_default_send_user_notify' => true,
    'core_tickets.newticket_enable_drafts'      => true,

    /*
     * True to add agents CC's in emails as followers
     */
    'core_tickets.add_agent_ccs' => false,

    'core_tickets.email_cc_max_count' => 20,

    /*
     * Process FW agent emails as forwards
     */
    'core_tickets.process_agent_fwd'       => true,
    'core_tickets.agent_fwd_subject_regex' => null,

    'core_tickets.lock_on_view'    => false,
    'core_tickets.unlock_on_close' => false,
    'core_tickets.lock_lifetime'   => 3600,

    'core_tickets.default_ticket_reverse_order' => true,

    'core_tickets.work_hours' => 'a:7:{s:8:"timezone";s:3:"UTC";s:10:"start_hour";i:9;s:9:"start_min";i:0;s:8:"end_hour";i:17;s:7:"end_min";i:0;s:8:"holidays";a:0:{}s:9:"work_days";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}',

    /*
     * The account to use when forwarding messages out
     */
    'core_tickets.fwd_use_account' => 0,

    /*
     * True to set the 'from' address on forwarded messages out
     */
    'core_tickets.fwd_use_agent_address' => false,

    /*
     * True to add email replies as notes instead of ticket replies.
     */
    'core_tickets.email_reply_as_note' => true,

    /*
     * True to add replies in fwd as notes instaed of replies
     */
    'core_tickets.email_fwd_reply_as_note' => true,

    //###################################################################################################################
    // portal
    //###################################################################################################################

    /*
     * The default brand
     */
    'portal.default_brand' => 1,

    /*
     * Use icon colors
     */
    'portal.use_icon_colors' => false,

    /*
     * A timestamp used in generating cache keys for permissions, etags, etc (updates automatically in the db)
     */
    'portal.global_cache_timestamp' => 0,

    /*
     * disable_permissions_cache
     */
    'portal.disable_permissions_cache' => false,

    /*
     * When creating a new feedback in portal, this status category is set automatically
     */
    'portal.default_feedback_status_category_id' => 1,

    /*
     * The default "per page" number of results in content lists
     */
    'portal.per_page_content' => 50,

    /*
     * The default "per page" number of results in RSS feeds
     */
    'portal.per_page_rss' => 50,

    /*
     * The default "per page" number of results in each of the user's ticket lists
     */
    'portal.per_page_tickets' => 50,

    /*
     * The default "per page" number of chats to show in the user's chat list page
     */
    'portal.per_page_chat' => 50,

    /*
     * If the portal should validate based on etags in the http cache layer
     * (EXPERIMENTAL - not to be used in production)
     */
    'portal.http_cache_etags' => false,

    /*
     * If the portal should validate based on last modified dates in the http cache layer
     * (EXPERIMENTAL - not to be used in production)
     */
    'portal.http_cache_last_modified' => false,

    /*
     * http s-maxage for a guest "page"
     */
    'portal.smaxage_guest_page' => 600,

    /*
     * http cache s-maxage for a guest "tag"
     */
    'portal.smaxage_guest_tag' => 600,

    /*
     * http cache s-maxage for a user "page"
     */
    'portal.smaxage_user_page' => 0,

    /*
     * http cache s-maxage for a user "tag"
     */
    'portal.smaxage_user_tag' => 0,

    //###################################################################################################################
    // core_misc
    //###################################################################################################################

    /*
     * How often to clean up login logs
     */
    'core_misc.cleanup_login_logs' => 2592000, // 30 days

    /*
     * How often to clean up old email gateway raw sources
     */
    'core_misc.cleanup_gateway_sources' => 2592000, // 30 days

    /*
     * Only clean up raw sources of closed tickets
     */
    'core_misc.cleanup_gateway_sources_onlyclosed' => true,

    /*
     * How often to clean up scheduled task log
     */
    'core_misc.cleanup_task_logs' => 604800, // 7 days

    /*
     * Server to use for rDNS lookups
     */
    'rdns_server' => '8.8.8.8',

    /*
     * How long to cache rdns lookups
     */
    'rdns_timeout' => '18000',

    /*
     * True to enable rdns on ticket messages when an IP is available
     */
    'rdns_ticket_messages' => false,

    /*
     * True to have hostnames visible on the ticket in a list rather that just
     * in the hover area
     */
    'rdns_ticket_showprops' => false,

    //###################################################################################################################
    // core_email
    //###################################################################################################################

    /*
     * The headers to use when detecting who sent an email (and the order in which to check)
     */
    'core_email.from_email_headers' => 'from,reply-to,x-original-from',

    'core_email.max_email_size' => 31457280, // 30 MB

    /*
     * How many times an email fails to send before it results
     * in a notification
     */
    'core_email.failed_email_attempts_notify' => 4,

    'core_email.antiflood_newtickets'      => 10,
    'core_email.antiflood_newtickets_warn' => 5,
    'core_email.antiflood_newtickets_time' => 900,

    'core_email.antiflood_newreplies'      => 10,
    'core_email.antiflood_newreplies_warn' => 5,
    'core_email.antiflood_newreplies_time' => 900,

    'core_email.enable_date_limit_rejection' => true,

    // TODO insert DeskPRO client_id and secret
    'core_email.google_oauth_client_id' => '',
    'core_email.google_oauth_secret'    => '',

    //###################################################################################################################
    // core_chat
    //###################################################################################################################

    /*
     * round_robin: Agents are assigned chats in round robin, least number of chats
     * everyone: Everyone sees the notification at the same time
     */
    'core_chat.assign_mode' => 'everyone', // round_robin, everyone

    /*
     * Number of seconds after an auto-assignment that the agent
     * has to acknowledge the chat until it's broadcast to everyone/next agent.
     */
    'core_chat.assign_ack_timeout' => 20,

    /*
     * Number of seconds until the agent times out and the chat is unassigned
     * Needs to be high enough not to provide false positives (e.g., cron needs to run regularly etc)
     */
    'core_chat.agent_timeout' => 120,

    /*
     * Number of seconds until the user times out
     */
    'core_chat.user_timeout' => 15,

    /*
     * When enabled, the user has to chose a department to start a chat
     */
    'core_chat.require_department' => 1,

    /*
     * Pageloads until proactive popup
     */
    'core_chat.proactive_pages' => 0,

    /*
     * Time until proactive popup
     */
    'core_chat.proactive_time' => 0,

    /*
     * The max amount of time a chat remains in un-assigned
     * state before we kill the chat and show user link to ticket
     */
    'core_chat.max_wait_time' => 900,

    /*
     * Max amount of time after a user timeout before the chat is closed for real
     */
    'core_chat.abandoned_time' => 480,

    /*
     * Time until we stop showing a user as "online" in online users list
     */
    'core_chat.user_online_time' => 600,

    /*
     * Number of simultaneous chats agents can usually handle (for queues)
     */
    'chat.max_chats_count' => 5,

    //###################################################################################################################
    // core_comments
    //###################################################################################################################

    /*
     * Require validation for guest-posted comments
     */
    'core_comments.enable_validation_guest' => true,

    /*
     * Require validation for comments posted by registered users
     */
    'core_comments.enable_validation_user' => true,

    //###################################################################################################################
    // core_downloads
    //###################################################################################################################

    /*
     * How many downloads until an article is considered popular?
     */
    'core_downloads.popular_downloads' => 50,

    /*
     * How new does an dl have to be to be "new"
     */
    'core_downloads.new_time' => '-1 month',

    //###################################################################################################################
    // core_feedback
    //###################################################################################################################

    /*
     * How many votes each person gets to spend
     */
    'core_feedback.votes_per_person' => 10,

    /*
     * The max votes a user can spend on one feedback
     */
    'core_feedback.max_votes_feedback' => 3,

    /*
     * Return votes to a user when an feedback is accepted?
     * Otherwise, return when the feedback is marked completed/deleted.
     */
    'core_feedback.return_on_accept' => false,

    /*
     * Require a user to login before an feedback can be submitted
     */
    'core_feedback.require_user' => false,

    /*
     * Require an agent to manually validate eacy submission
     */
    'core_feedback.require_validation' => false,

    /*
     * How many votes until an feedback is 'popular'?
     */
    'core_feedback.popular_votes' => 10,

    /*
     * Show feedback publicly even when they're validating?
     */
    'core_feedback.show_validating' => 1,

    //###################################################################################################################
    // core_kb
    //###################################################################################################################

    /*
     * How many views until an article is considered popular?
     */
    'core_kb.popular_views' => 3,

    /*
     * How new does an article have to be to be "new"
     */
    'core_kb.new_time' => '-1 month',

    //###################################################################################################################
    // agent
    //###################################################################################################################

    'agent.max_login_attempts' => 5,
    'agent.login_lockout_time' => 3600,
    'agent.login_logo_blob_id' => null,

    'agent.notify_self_login'        => false,
    'agent.notify_self_failed_login' => true,

    'agent.notify_login_emaillist'        => '',
    'agent_notify_list_login'             => false,
    'agent_notify_list_failed_login'      => false,
    'agent_notify_list_adminlogin'        => false,
    'agent_notify_list_failed_adminlogin' => true,

    'agent.ui_snippets_use_client_db' => false,

    /*
     * How long in seconds before old alerts are cleaned up
     */
    'agent.alerts_cleanup_time' => 129600,

    /*
     * Max age of undismissed alerts before they are cleaned up
     */
    'agent.alerts_cleanup_time_always' => 604800,

    //###################################################################################################################
    // user
    //###################################################################################################################

    // alias for core.iface_portal
    'user.portal_enabled'          => true,
    'user.portal_title'            => 'Support Center',
    'user.portal_header'           => 'My Title',
    'user.portal_tagline'          => 'My Tagline',
    'user.portal_simpleheader'     => false,
    'user.portal_tab_news'         => 1,
    'user.portal_tab_articles'     => 1,
    'user.portal_tab_feedback'     => 1,
    'user.portal_tab_tickets'      => 1,
    'user.portal_tab_downloads'    => 1,
    'user.portal_tab_guides'       => 1,
    'user.portal_tabs_order'       => 'articles,guides,news,feedback,downloads,newticket',
    'user.disable_chat_element'    => false,
    'user.portal_default_news_cat' => 0,

    /*
     * Invalidate the "password reset" code after this many seconds
     */
    'user.password_reset_code_time_limit' => 18000,

    'user.show_ratings'             => true,
    'user.show_ratings_min_votes'   => 1,
    'user.publish_comments'         => true,
    'user.feedback_notify_comments' => true,
    'user.kb_subscriptions'         => true,
    'user.news_subscriptions'       => true,
    'user.downloads_subscriptions'  => true,
    'user.feedback_subscriptions'   => true,
    'user.kb_categories_with_tree'  => true,

    //###################################################################################################################
    // captcha : FALSE (off), 'guests', 'everyone'
    //###################################################################################################################

    'user.captcha.tickets'  => false,
    'user.captcha.comments' => false,
    'user.captcha.feedback' => false,
    'user.captcha.register' => false,
    'user.captcha.sharing'  => false,

    'core.use_recaptcha2'        => false,
    'core.recaptcha2_site_key'   => '',
    'core.recaptcha2_secret_key' => '',

    //###################################################################################################################
    // search
    //###################################################################################################################

    // Determines if the elastic stuff is available for use
    'elastica.allow_elastic' => false,

    // Use elastic search?
    'elastica.enabled' => false,

    // This is true when elastic was just enabled and the admin needs to exec
    // the cmd to reset the index to initialize it
    'elastica.requires_reset' => false,

    'elastica.clients.default.host' => 'localhost',
    'elastica.clients.default.port' => 9200,

    //###################################################################################################################
    // password policy
    //###################################################################################################################

    'user.password_policy.min_length'            => 5,
    'user.password_policy.max_age'               => 0,
    'user.password_policy.forbid_reuse'          => false,
    'user.password_policy.require_num_uppercase' => 0,
    'user.password_policy.require_num_lowercase' => 0,
    'user.password_policy.require_num_number'    => 0,
    'user.password_policy.require_num_symbol'    => 0,

    'agent.password_policy.min_length'            => 5,
    'agent.password_policy.max_age'               => 0,
    'agent.password_policy.forbid_reuse'          => false,
    'agent.password_policy.require_num_uppercase' => 0,
    'agent.password_policy.require_num_lowercase' => 0,
    'agent.password_policy.require_num_number'    => 0,
    'agent.password_policy.require_num_symbol'    => 0,

    'agent.ip_security.enabled'            => false,
    'agent.ip_security.mode'               => 'agents,admins',
    'agent.ip_security.whitelist_lifetime' => 1814400,

    //###################################################################################################################
    // rate limit
    //###################################################################################################################
    'rate_limit.share_content.enabled'      => true,
    'rate_limit.share_content.limit'        => 3,
    'rate_limit.share_content.time'         => 15 * 60, // 15 min
    'rate_limit.share_content.lockout_time' => 15 * 60, // 15 min
    'rate_limit.share_content.response'     => 'captcha',

    'rate_limit.upload_attachment.enabled'      => true,
    'rate_limit.upload_attachment.limit'        => 50,
    'rate_limit.upload_attachment.time'         => 15 * 60, // 15 min
    'rate_limit.upload_attachment.lockout_time' => 15 * 60, // 15 min
    'rate_limit.upload_attachment.response'     => 'lockout',

    'rate_limit.login.agent.enabled'      => true,
    'rate_limit.login.agent.limit'        => 5,
    'rate_limit.login.agent.time'         => 15 * 60, // 15 min
    'rate_limit.login.agent.lockout_time' => 15 * 60, // 15 min
    'rate_limit.login.agent.response'     => 'lockout',

    'rate_limit.login.enabled'      => true,
    'rate_limit.login.limit'        => 6,
    'rate_limit.login.time'         => 15 * 60, // 15 min
    'rate_limit.login.lockout_time' => 15 * 60, // 15 min
    'rate_limit.login.response'     => 'lockout',

    'rate_limit.login.guest.enabled'      => true,
    'rate_limit.login.guest.limit'        => 3,
    'rate_limit.login.guest.time'         => 60 * 60, // 15 min
    'rate_limit.login.guest.lockout_time' => 15 * 60, // 15 min
    'rate_limit.login.guest.response'     => 'captcha',

    'rate_limit.registration.enabled'      => false,
    'rate_limit.registration.limit'        => 3,
    'rate_limit.registration.time'         => 15 * 60, // 15 min
    'rate_limit.registration.lockout_time' => 15 * 60, // 15 min

    'rate_limit.reset_password.enabled'      => false,
    'rate_limit.reset_password.limit'        => 3,
    'rate_limit.reset_password.time'         => 15 * 60, // 15 min
    'rate_limit.reset_password.lockout_time' => 15 * 60, // 15 min

    'rate_limit.token_exchange.enabled'      => true,
    'rate_limit.token_exchange.limit'        => 50,
    'rate_limit.token_exchange.time'         => 15 * 60, // 15 min
    'rate_limit.token_exchange.lockout_time' => 15 * 60, // 15 min
    'rate_limit.token_exchange.response'     => 'captcha',

    'rate_limit.submit_comment.enabled'      => true,
    'rate_limit.submit_comment.limit'        => 3,
    'rate_limit.submit_comment.time'         => 15 * 60, // 15 min
    'rate_limit.submit_comment.lockout_time' => 15 * 60, // 15 min
    'rate_limit.submit_comment.response'     => 'captcha',

    'rate_limit.submit_feedback.enabled'      => true,
    'rate_limit.submit_feedback.limit'        => 3,
    'rate_limit.submit_feedback.time'         => 15 * 60, // 15 min
    'rate_limit.submit_feedback.lockout_time' => 15 * 60, // 15 min
    'rate_limit.submit_feedback.response'     => 'captcha',

    'rate_limit.submit_ticket.enabled'      => true,
    'rate_limit.submit_ticket.limit'        => 3,
    'rate_limit.submit_ticket.time'         => 15 * 60, // 15 min
    'rate_limit.submit_ticket.lockout_time' => 15 * 60, // 15 min
    'rate_limit.submit_ticket.response'     => 'captcha',

    'rate_limit.share_content.guest.enabled'      => true,
    'rate_limit.share_content.guest.limit'        => 3,
    'rate_limit.share_content.guest.time'         => 15 * 60, // 15 min
    'rate_limit.share_content.guest.lockout_time' => 15 * 60, // 15 min
    'rate_limit.share_content.guest.response'     => 'captcha',

    'rate_limit.upload_attachment.guest.enabled'      => true,
    'rate_limit.upload_attachment.guest.limit'        => 50,
    'rate_limit.upload_attachment.guest.time'         => 15 * 60, // 15 min
    'rate_limit.upload_attachment.guest.lockout_time' => 15 * 60, // 15 min
    'rate_limit.upload_attachment.guest.response'     => 'lockout',

    'rate_limit.submit_comment.guest.enabled'      => true,
    'rate_limit.submit_comment.guest.limit'        => 3,
    'rate_limit.submit_comment.guest.time'         => 15 * 60, // 15 min
    'rate_limit.submit_comment.guest.lockout_time' => 15 * 60, // 15 min
    'rate_limit.submit_comment.guest.response'     => 'captcha',

    'rate_limit.submit_feedback.guest.enabled'      => true,
    'rate_limit.submit_feedback.guest.limit'        => 3,
    'rate_limit.submit_feedback.guest.time'         => 15 * 60, // 15 min
    'rate_limit.submit_feedback.guest.lockout_time' => 15 * 60, // 15 min
    'rate_limit.submit_feedback.guest.response'     => 'captcha',

    'rate_limit.submit_ticket.guest.enabled'      => true,
    'rate_limit.submit_ticket.guest.limit'        => 3,
    'rate_limit.submit_ticket.guest.time'         => 15 * 60, // 15 min
    'rate_limit.submit_ticket.guest.lockout_time' => 15 * 60, // 15 min
    'rate_limit.submit_ticket.guest.response'     => 'captcha',

    //###################################################################################################################
    // notification.settings
    //###################################################################################################################

    /*
     * Here you CAN define strategies for different notifications.
     * E.g. you can deliver messages from IM immediately via pusher application
     * or ticket updates only via db.
     * However you can use several methods to deliver one event - but you should avoid it,
     * because it could be unstable just right now.
     */
    'notification.settings.strategies' => [
        //'notification.agent_chat.new_message' => [
        //    'strategy' => 'immediate',
        //    'delivery' => [
        //        'db',
        //    ],
        //],
        //        'notification.yet.another.system.event' => [
        //            'strategy' => 'deferred',
        //            'delivery' => [
        //                'db',
        //            ],
        //            'persistance' => 'db',
        //        ],
    ],

    // Also you MUST provide default strategy. It will be used to handle events that was not described.
    'notification.settings.default_strategy' => [
        'strategy' => 'immediate',
        'delivery' => [
            // 'db' or 'pusher'
            'db',
        ],
    ],

    // You MUST provide pusher application settings if you plan to use it.
    // You SHOULD place it in your config.php file
    'notification.settings.pusher_client.appKey'  => '',
    'notification.settings.pusher_client.secret'  => '',
    'notification.settings.pusher_client.appId'   => '',
    'notification.settings.pusher_client.cluster' => 'mt1',
    'notification.settings.pusher_client.debug'   => false,
    'notification.settings.pusher_client.timeout' => 5,
    'notification.settings.pusher_client.tries'   => 2,

    // Simple delivery handler with polling
    'notification.settings.polling_client.polling_interval' => 5000,

    //###################################################################################################################
    // widget.settings
    //###################################################################################################################

    'portal.chat.email_validation' => false,
    'portal.chat.require_login'    => false,

    //###################################################################################################################
    // api_log
    //###################################################################################################################

    // global version id for Etag generating. Change this and whole your api cache would become stale.
    'response.cache.global_version' => '7dedef53d7b6762e2ad984051e37638d',

    'response.cache.enabled' => false,

    'api_log.enabled'     => false,
    'api_log.modes'       => ['key'],
    'api_log.dupe.modes'  => ['key'],
    'api_log.writer.type' => 'db',
    'api_log.writer.file' => [
        'log_max_size'  => 5 * 1024 * 1024,
        'log_max_files' => 5,
        'log_name'      => 'api_log.log',
    ],
    'api_log.writer.file.serializer.type' => 'serialize',

    'api_limits.global.hour' => -1,
    'api_limits.global.day'  => -1,

    'api_limits.key.hour'    => -1,
    'api_limits.key.day'     => -1,
    'api_limits.key.default' => -1,

    //###################################################################################################################
    // audit_log
    //###################################################################################################################

    'audit_log.storage' => 'db',

    'audit_log.configuration' => require('audit_log.settings.php'),

    //###################################################################################################################
    // auto upgrader
    //###################################################################################################################

    /*
     * Enable/disable the automatic upgrader
     */
    'auto_updater_enabled' => false,

    /*
     * How often to check/install updates
     */
    'auto_updater_interval_days' => 1,

    /*
     * When to install updates
     */
    'auto_updater_time_of_day'    => '01:00',
    'auto_updater_time_of_day_tz' => 'UTC',

    /*
     * Date and time of next scheduled check
     */
    'auto_updater_next_check' => null,

    'api.disable_location_header_strip' => false,

    /*
     * Email logs cleanup
     */
    'email_log.cleanup.delay_days' => 3,

    /*
     * The hostname of deskpro's own oauth2 proxy
     */
    'dpoauth2proxy.host' => 'auth.deskpro.com',

    //###################################################################################################################
    // services
    //###################################################################################################################

    'services.language_sync_api' => 'https://lang-sync-services.deskpro.com/',
];
