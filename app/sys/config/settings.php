<?php return array(

    ####################################################################################################################
    # core
    ####################################################################################################################

    /**
     * When there's a problem where we cant get user input any other way, this address will be displayed.
     */
    'core.emergency_contact' => 'emergency-contact@example.com',

    /**
     * Is registration enbaled?
     */
    'core.reg_enabled' => true,

    /**
     * Is registration required?
     */
    'core.reg_required' => false,

    /**
     * Is email validation enabled?
     */
    'core.email_validation' => false,

    /**
     * Is agent validation enabled?
     */
    'core.agent_validation' => false,

    /**
     * Enable URL rewriting (removes index.php/ from URLs)
     */
    'core.rewrite_urls' => false,

    /**
     * Is multi-language features enabeld?
     */
    'core.enable_languages' => true,

    /**#@+
     * Date formats
     */
    'core.date_fulltime' => 'D, jS M Y g:ia',
    'core.date_full' => 'D, jS M Y',
    'core.date_day'  => 'M j Y',
    'core.date_day_short'  => 'M j',
    'core.date_time' => 'g:i a',
    /**#@-*/

    /**
     * Are ticket categories enabled?
     */
    'core.use_ticket_category' => false,

    /**
     * Are ticket priorities enabled?
     */
    'core.use_ticket_priority' => false,

    /**
     * Are ticket workflows enabled?
     */
    'core.use_ticket_workflow' => false,

    /**
     * Are agent teams enabled?
     */
    'core.use_agent_team' => false,

    /**
     * Are products enabled?
     */
    'core.use_product' => false,

    /**
     * Are custom fields on products enabled?
     */
    'core.use_product_fields' => false,

    /**
     * Is the helpdesk disabled? A disabled helpdesk doesn't fully boot and shows a maintenance message.
     */
    'core.helpdesk_disabled' => false,

    /**
     * The default message to show when helpdesk is disabled
     * Note that this is actually written to a file when updated, this record is kept just
     * as the default.
     */
    'core.helpdesk_disabled_message' => 'Our helpdesk is temporarily offline for maintenance. We will be back up in about 10 minutes.',

    /**
     * The URL to the DeskPRO installation.
     * Should include trailing slash. Should NOT include index.php portion.
     */
    'core.deskpro_url' => '',

    /**
     * The name of the DeskPRO helpdesk
     */
    'core.deskpro_name' => 'DeskPRO',

    /**
     * The optional tagline or sub-title of the helpdesk
     */
    'core.deskpro_tagline' => '',

    /**
     * The blobid of the uploaded logo image
     */
    'core.deskpro_logo_blob' => 0,

    /**
     * The optional URL to a main website
     */
    'core.site_url' => '',

    /**
     * The optional title for the site URL
     */
    'core.site_name' => '',

    /**
     * The secret string used for this installation used when generating various hashes
     */
    'core.app_secret' => 'APP_SECRET',

    /**
     * The default "from" address to send all email from
     */
    'core.default_from_email' => '',

    /**
     * Optionally BCC all emails to this address
     */
    'core.bcc_all_emails' => '',

    /**
     * How long, in days, to save successfully sent emails to
     */
    'core.store_sent_mail_days' => 0,

    /**
     * When the installation took place
     */
    'core.install_time' => 0,

    /**
     * The site ID. Only used when multiple helpdesks need to talk to eachother.
     */
    'core.site_id' => '1',

    /**
     * When enabled, error reports and heartbeats dont send server stats,
     * and heartbeats dont send db stats
     */
    'core.enable_reduced_lic_reports' => false,

    /**
     * The cookie path
     */
    'core.cookie_path' => '/',

    /**
     * The cookie domains
     */
    'core.cookie_domain' => '',

    /**
     * The language to use by default when a user has no selection
     */
    'core.default_language_id' => 1,

    /**
     * The style to use
     */
    'core.default_style_id' => 1,

    /**
     * The timezone to use by default when a user has no selection
     */
    'core.default_timezone' => 'UTC',

    /**
     * The default 2-letter country code (ISO 3166-1 alpha-2 format)
     */
    'core.default_country_code' => 'US',

    /**
     * Use gravatar for default avatars?
     */
    'core.use_gravatar' => 0,

    /**
     * Enable remember-me for agents?
     */
    'core.enable_agent_rememberme' => true,

    /**
     * Enable remember-me for users?
     */
    'core.enable_user_rememberme' => true,

    /**
     * How long, in seconds, are sessions valid for?
     */
    'core.sessions_lifetime' => 3600,

    /**
     * Require an actual pageload to keep a session alive (e.g., automatic pings dont keep sessions alive)
     */
    'core.session_keepalive_require_page' => false,

    /**
     * How long, in seconds, are email sources kept for before being cleaned up
     */
    'core.email_source_storetime' => 5184000, // 60 days
    'core.email_source_storetime_error' => 15552000, // 180 days
    'core.email_source_storetime_rejection' => 1296000, // 15 days

    /**
     * How long to store ticket manager logs for
     */
    'core.ticket_manager_log_storetime' => 604800, // 7 days

    /**
     * When to use the mail queue: never, hint, always
     */
    'core.use_mail_queue'  => 'hint',

    //'core.disqus_shortname' => '',
    //'core.facebook_comments_num_posts' => 10,
    //'core.facebook_admins' => '',
    //'core.facebook_like' => false,

    /**
     * Which comment adapter to use: false=deskpro, disqus or facebook
     */
    'core.comments_adapter' => false,

    /**
     * Recaptcha public key
     */
    'core.recaptcha_public_key'  => '6LcWL8YSAAAAAJu1CrtS9RdOJyKd_NbArNgUFWV9',

    /**
     * Recaptcha private key
     */
    'core.recaptcha_private_key' => '6LcWL8YSAAAAAJVZ5AEMb5Vq7wkZoGHfKfAqvB2U',

    /**
     * Default storage method for blobs is the db. Other values: fs
     */
    'core.filestorage_method' => 'db',

    /**
     * Require a user to log in if they enter an email address that is owned by an existing accounts
     */
    'core.existing_account_login' => false,

    /**
     * The max attachment an agent can upload
     */
    'core.attach_agent_maxsize' => '5242880',

    /**
     * The only file extensions that an agent can upload (whitelist)
     */
    'core.attach_agent_must_exts' => null,

    /**
     * File extensions that an agent is forbidden from uploading (blacklist)
     */
    'core.attach_agent_not_exts' => null,

    /**
     * The max attachment a user can upload
     */
    'core.attach_user_maxsize' => '5242880',

    /**
     * The only file extensions that users can upload (whitelist)
     */
    'core.attach_user_must_exts' => null,

    /**
     * File extensions that a user is forbidden from uploading (blacklist)
     */
    'core.attach_user_not_exts' => null,

    'core.emails.attach_agent_maxsize' => '26214400',
    'core.emails.attach_agent_must_exts' => null,
    'core.emails.attach_agent_not_exts' => null,
    'core.emails.attach_user_maxsize' => '26214400',
    'core.emails.attach_user_must_exts' => null,
    'core.emails.attach_user_not_exts' => null,

    /**
     * Number of emails that trigger the rate limit
     */
    'core.emails.rate_count'     => 15,

    /**
     * The time in which the above number of emails are submitted
     * before we lock.
     */
    'core.emails.rate_time'      => 600,

    /**
     * How long to lock for once the limit is passed.
     */
    'core.emails.rate_locktime'  => 900,

    /**
     * True to have the DeskPRO local user source enabled
     */
    'core.deskpro_source_enabled' => true,

    /**
     * True if we always replace local name (fname, lname, name) with usersource name on every login if usersource provides one.
     * False means we only do this on first login with that usersource.
     */
    'core.usersource_login_always_update_name' => true,

    /**
     * True to have links from chat intercepted and sent through the security page
     */
    'core.agent_intercept_external_link' => false,

    /**
     * Show the share widget (twitter/facebook/linkedin/gplus)
     */
    'core.show_share_widget' => true,
    'core.show_share_facebook' => true,
    'core.show_share_twitter' => true,
    'core.show_share_linkedin' => true,
    'core.show_share_gplus' => true,

    /**
     * Enable the KB?
     */
    'core.apps_kb' => 1,

    /**
     * Enable feedback?
     */
    'core.apps_feedback' => 1,

    /**
     * Enable chat?
     */
    'core.apps_chat' => 1,

    /**
     * Enable agent tasks?
     */
    'core.apps_tasks' => 1,

    /**
     * Enable news
     */
    'core.apps_news' => 1,

    /**
     * Enable downloads
     */
    'core.apps_downloads' => 1,

    /**
     * Maximum SMS chunks to send for a single message
     */
    'core.max_sms_chunks' => 5,

    /**
     * Max email size to read from gateways
     */
    'core.gateway_max_email' => 41943040,

    /**
     * True to disable floodchecking in gateway
     */
    'core.disable_gateway_floodcheck' => false,

    /**
     * Show suggestions newticket
     */
    'core.show_ticket_suggestions' => true,

    /**
     * Max size of attachments to send in email notifications
     */
    'core.sendemail_attach_maxsize' => 7340032,

    /**
     * Automatically install new languages on upgrade
     */
    'core.lang_auto_install' => false,

    /**
     * How long, in seconds, are drafts valid for?
     */
    'core.drafts_lifetime' => 604800, // 1 week

    /**
     * A Google Analytics property ID (UA-XXXXX-Y). If added, GA will be enabled on user pages.
     */
    'core.ga_property_id' => '',

    /**
     * Interval to limit allowed requests to API in seconds
     */
    'core.api_rate_limit_interval' => 60,
    /**
     * Number of allowed requests to the API per interval. 0 to disable.
     */
    'core.api_rate_limit' => defined('DPC_IS_CLOUD') ? 1000 : 0,

    'core.twitter_agent_consumer_key' => '',
    'core.twitter_agent_consumer_secret' => '',
    'core.twitter_user_consumer_key' => '',
    'core.twitter_user_consumer_secret' => '',
    'core.twitter_auto_remove_time' => 1209600,

    /**
     * Time in H:i format for when to send task notifs.
     * The time is considered to be of the default timezone.
     */
    'core.task_reminder_time' => '09:00',

    /**
     * How long to keep visitor tracks around for
     */
    'core.visitor_cleanup_time' => 604800,

    /**
     * How long to keep visitor tracks around for
     * when they are suspected dupes/bots/unconnected
     */
    'core.visitor_cleanup_bogus_time' => 1800,

    'core.agent_translate_debug' => false,
    'core.agent_enable_kb_shortcuts' => true,

    ####################################################################################################################
    # core_tickets
    ####################################################################################################################

    'core_tickets.lock_timeout' => 600,
    'core_tickets.hard_delete_time' => 2419000,
    'core_tickets.spam_delete_time' => 2419000,

    'core_tickets.enable_feedback' => 1,
    'core_tickets.feedback_agents_read' => 1,

    'core_tickets.enable_timelog' => 0,
    'core_tickets.enable_billing' => 0,
    'core_tickets.billing_on_reply' => 0,
    'core_tickets.billing_auto_timer' => 0,
    'core_tickets.billing_on_new' => 0,
    'core_tickets.billing_auto_timer_new' => 0,
    'core_tickets.billing_currency' => 'USD',

    'core_tickets.enable_agent_rte' => true,
    'core_tickets.agent_rte_button_html' => false,
    'core_tickets.agent_rte_button_bold' => true,
    'core_tickets.agent_rte_button_italic' => true,
    'core_tickets.agent_rte_button_underline' => true,
    'core_tickets.agent_rte_button_strike' => false,
    'core_tickets.agent_rte_button_color' => true,
    'core_tickets.agent_rte_button_list' => true,
    'core_tickets.agent_rte_button_image' => true,
    'core_tickets.agent_rte_button_link' => true,
    'core_tickets.agent_rte_button_table' => false,
    'core_tickets.agent_rte_button_hr' => true,
    'core_tickets.agent_rte_button_alignment' => false,

    'core_tickets.use_ref' => false,

    'core_tickets.gateway_enable_subject_match' => true,

    'core_tickets.email_history_limit' => 11, // 10 + 1 for the original message at top

    'core.allow_arbitrary_gateway_address' => 1,

    'core_tickets.use_archive' => 0,
    'core_tickets.auto_archive_time' => 2419000,

    'core_tickets.enable_like_search_mode' => true,

    // See TicketMessage::checkDupeMessage
    'core_tickets.enable_dupe_checking' => true,

    // See TicketGatewayProcessor::createTicketDetector and SubjectMatchDetector::enableExactSubjectMatching
    'core_tickets.enable_exact_subject_matching' => false,

    // True to force agent emails to have the marker line
    'core_tickets.gateway_agent_require_marker' => true,

    'core_tickets.reply_status' => 'awaiting_user',
    'core_tickets.reply_assign_unassigned' => 'assign',
    'core_tickets.reply_assign_assigned' => false,
    'core_tickets.reply_assignteam_unassigned' => false,
    'core_tickets.reply_assignteam_assigned' => false,
    'core_tickets.reassign_auto_change_status' => false,
    'core_tickets.resolve_auto_close_tab' => false,
    'core_tickets.new_status' => 'awaiting_user',
    'core_tickets.new_assign' => 'assign',
    'core_tickets.new_assignteam' => false,
    'core_tickets.default_send_user_notify' => true,
    'core_tickets.new_default_send_user_notify' => true,

    /**
     * True to add agents CC's in emails as followers
     */
    'core_tickets.add_agent_ccs' => false,

    /**
     * Process FW agent emails as forwards
     */
    'core_tickets.process_agent_fwd' => true,
    'core_tickets.agent_fwd_subject_regex' => null,

    'core_tickets.lock_on_view' => false,
    'core_tickets.unlock_on_close' => false,
    'core_tickets.lock_lifetime' => 3600,

    'core_tickets.default_ticket_reverse_order' => true,

    'core_tickets.work_hours' => 'a:7:{s:8:"timezone";s:3:"UTC";s:10:"start_hour";i:9;s:9:"start_min";i:0;s:8:"end_hour";i:17;s:7:"end_min";i:0;s:8:"holidays";a:0:{}s:9:"work_days";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;}}',

    /**
     * The account to use when forwarding messages out
     */
    'core_tickets.fwd_use_account' => 0,

    /**
     * True to set the 'from' address on forwarded messages out
     */
    'core_tickets.fwd_use_agent_address' => false,


    ####################################################################################################################
    # portal
    ####################################################################################################################

    /**
     * The default brand
     */
    'portal.default_brand' => 1,

    /**
     * A timestamp used in generating cache keys (updates automatically in the db)
     */
    'portal.default_permissions_timestamp' => 0,

    /**
     * When creating a new feedback in portal, this status category is set automatically
     */
    'portal.default_feedback_status_category_id' => 2,

    /**
     * The default "per page" number of results in content lists
     */
    'portal.per_page_content' => 10,

    /**
     * The default "per page" number of results in RSS feeds
     */
    'portal.per_page_rss' => 10,

    /**
     * The default "per page" number of results in each of the user's ticket lists
     */
    'portal.per_page_tickets' => 10,

    /**
     * If the portal should validate based on etags in the http cache layer
     */
    'portal.http_cache_etags' => true,

    /**
     * If the portal should validate based on last modified dates in the http cache layer
     */
    'portal.http_cache_last_modified' => true,

    /**
     * http s-maxage for a guest "page"
     */
    'portal.smaxage_guest_page' => 45,

    /**
     * http cache s-maxage for a guest "tag"
     */
    'portal.smaxage_guest_tag' => 45,

    /**
     * http cache s-maxage for a user "page"
     */
    'portal.smaxage_user_page' => 45,

    /**
     * http cache s-maxage for a user "tag"
     */
    'portal.smaxage_user_tag' => 45,


    ####################################################################################################################
    # core_misc
    ####################################################################################################################

    /**
     * How often to clean up Visitor records
     */
    'core_misc.cleanup_visitors' => 864000, // 10 days

    /**
     * How often to clean up login logs
     */
    'core_misc.cleanup_login_logs' => 2592000, // 30 days

    /**
     * How often to clean up old email gateway raw sources
     */
    'core_misc.cleanup_gateway_sources' => 2592000, // 30 days

    /**
     * Only clean up raw sources of closed tickets
     */
    'core_misc.cleanup_gateway_sources_onlyclosed' => true,

    /**
     * How often to clean up scheduled task log
     */
    'core_misc.cleanup_task_logs' => 604800, // 7 days

    /**
     * Server to use for rDNS lookups
     */
    'rdns_server' => '8.8.8.8',

    /**
     * How long to cache rdns lookups
     */
    'rdns_timeout' => '18000',

    /**
     * True to enable rdns on ticket messages when an IP is available
     */
    'rdns_ticket_messages' => false,

    /**
     * True to have hostnames visible on the ticket in a list rather that just
     * in the hover area
     */
    'rdns_ticket_showprops' => false,

    ####################################################################################################################
    # core_email
    ####################################################################################################################

    /**
     * The headers to use when detecting who sent an email (and the order in which to check)
     */
    'core_email.from_email_headers' => 'from,reply-to,x-original-from',

    'core_email.max_email_size' => 31457280, // 30 MB

    /**
     * How many times an email fails to send before it results
     * in a notification
     */
    'core_email.failed_email_attempts_notify' => 4,


    'core_email.antiflood_newtickets' => 10,
    'core_email.antiflood_newtickets_warn' => 5,
    'core_email.antiflood_newtickets_time' => 900,

    'core_email.antiflood_newreplies' => 10,
    'core_email.antiflood_newreplies_warn' => 5,
    'core_email.antiflood_newreplies_time' => 900,

    'core_email.enable_date_limit_rejection' => true,

    ####################################################################################################################
    # core_chat
    ####################################################################################################################

    /**
     * round_robin: Agents are assigned chats in round robin, least number of chats
     * everyone: Everyone sees the notification at the same time
     */
    'core_chat.assign_mode' => 'everyone', // round_robin, everyone

    /**
     * Number of seconds after an auto-assignment that the agent
     * has to acknowledge the chat until it's broadcast to everyone/next agent.
     */
    'core_chat.assign_ack_timeout' => 20,

    /**
     * Number of seconds until the agent times out and the chat is unassigned
     * Needs to be high enough not to provide false positives (e.g., cron needs to run regularly etc)
     */
    'core_chat.agent_timeout' => 120,

    /**
     * Number of seconds until the user times out
     */
    'core_chat.user_timeout' => 15,

    /**
     * When enabled, the user has to chose a department to start a chat
     */
    'core_chat.require_department' => 1,

    /**
     * Pageloads until proactive popup
     */
    'core_chat.proactive_pages' => 0,

    /**
     * Time until proactive popup
     */
    'core_chat.proactive_time' => 0,

    /**
     * The max amount of time a chat remains in un-assigned
     * state before we kill the chat and show user link to ticket
     */
    'core_chat.max_wait_time' => 900,

    /**
     * Max amount of time after a user timeout before the chat is closed for real
     */
    'core_chat.abandoned_time' => 480,

    /**
     * Time until we stop showing a user as "online" in online users list
     */
    'core_chat.user_online_time' => 600,

    ####################################################################################################################
    # core_comments
    ####################################################################################################################

    /**
     * Require validation for guest-posted comments
     */
    'core_comments.enable_validation_guest' => true,

    /**
     * Require validation for comments posted by registered users
     */
    'core_comments.enable_validation_user' => true,

    ####################################################################################################################
    # core_downloads
    ####################################################################################################################

    /**
     * How many downloads until an article is considered popular?
     */
    'core_downloads.popular_downloads' => 50,

    /**
     * How new does an dl have to be to be "new"
     */
    'core_downloads.new_time' => '-1 month',

    ####################################################################################################################
    # core_feedback
    ####################################################################################################################

    /**
     * How many votes each person gets to spend
     */
    'core_feedback.votes_per_person' => 10,

    /**
     * The max votes a user can spend on one feedback
     */
    'core_feedback.max_votes_feedback' => 3,

    /**
     * Return votes to a user when an feedback is accepted?
     * Otherwise, return when the feedback is marked completed/deleted.
     */
    'core_feedback.return_on_accept' => false,

    /**
     * Require a user to login before an feedback can be submitted
     */
    'core_feedback.require_user' => false,

    /**
     * Require an agent to manually validate eacy submission
     */
    'core_feedback.require_validation' => false,

    /**
     * How many votes until an feedback is 'popular'?
     */
    'core_feedback.popular_votes' => 10,

    /**
     * Show feedback publicly even when they're validating?
     */
    'core_feedback.show_validating' => 1,

    ####################################################################################################################
    # core_kb
    ####################################################################################################################

    /**
     * How many views until an article is considered popular?
     */
    'core_kb.popular_views' => 3,

    /**
     * How new does an article have to be to be "new"
     */
    'core_kb.new_time' => '-1 month',

    ####################################################################################################################
    # agent
    ####################################################################################################################

    'agent.max_login_attempts' => 5,
    'agent.login_lockout_time' => 3600,
    'agent.login_logo_blob_id' => null,

    'agent.notify_self_login' => false,
    'agent.notify_self_failed_login' => true,

    'agent.notify_login_emaillist' => '',
    'agent_notify_list_login' => false,
    'agent_notify_list_failed_login' => false,
    'agent_notify_list_adminlogin' => false,
    'agent_notify_list_failed_adminlogin' => true,

    'agent.ui_snippets_use_client_db' => false,

    /**
     * How long in seconds before old alerts are cleaned up
     */
    'agent.alerts_cleanup_time' => 129600,

    /**
     * Max age of undismissed alerts before they are cleaned up
     */
    'agent.alerts_cleanup_time_always' => 604800,

    ####################################################################################################################
    # user
    ####################################################################################################################

    'user.portal_enabled' => true,
    'user.portal_title' => 'Support Center',
    'user.portal_header' => 'My Title',
    'user.portal_tagline' => 'My Tagline',
    'user.portal_simpleheader' => false,
    'user.portal_tab_news' => 1,
    'user.portal_tab_articles' => 1,
    'user.portal_tab_feedback' => 1,
    'user.portal_tab_tickets' => 1,
    'user.portal_tab_downloads' => 1,
    'user.portal_tabs_order' => 'articles,downloads,feedback,newticket,news',
    'user.disable_chat_element' => false,
    'user.portal_default_news_cat' => 0,

    /**
     * Invalidate the "password reset" code after this many seconds
     */
    'user.password_reset_code_time_limit' => 86400,

    'user.show_ratings' => true,
    'user.show_ratings_min_votes' => 1,
    'user.show_num_votes' => false,
    'user.publish_captcha' => true,
    'user.publish_comments' => true,
    'user.register_captcha' => true,
    'user.always_show_captcha' => false,
    'user.feedback_notify_comments' => true,
    'user.kb_subscriptions' => true,
    'user.news_subscriptions' => true,
    'user.downloads_subscriptions' => true,

    ####################################################################################################################
    # search
    ####################################################################################################################

    // Determines if the elastic stuff is available for use
    'elastica.allow_elastic' => false,

    // Use elastic search?
    'elastica.enabled' => false,

    // This is true when elastic was just enabled and the admin needs to exec
    // the cmd to reset the index to initialize it
    'elastica.requires_reset' => false,

    'elastica.clients.default.host' => 'localhost',
    'elastica.clients.default.port' => 9200,

    ####################################################################################################################
    # password policy
    ####################################################################################################################

    'user.password_policy.min_length' => 5,
    'user.password_policy.max_age' => 0,
    'user.password_policy.forbid_reuse' => false,
    'user.password_policy.require_num_uppercase' => 0,
    'user.password_policy.require_num_lowercase' => 0,
    'user.password_policy.require_num_number' => 0,
    'user.password_policy.require_num_symbol' => 0,

    'agent.password_policy.min_length' => 5,
    'agent.password_policy.max_age' => 0,
    'agent.password_policy.forbid_reuse' => false,
    'agent.password_policy.require_num_uppercase' => 0,
    'agent.password_policy.require_num_lowercase' => 0,
    'agent.password_policy.require_num_number' => 0,
    'agent.password_policy.require_num_symbol' => 0,

    'agent.ip_security.enabled'            => false,
    'agent.ip_security.mode'               => 'agents,admins',
    'agent.ip_security.whitelist_lifetime' => 1814400,

    ####################################################################################################################
    # login rate limit
    ####################################################################################################################

    'user.login_rate_limit.enabled'         => true,
    'user.login_rate_limit.attempts'        => 20,
    'user.login_rate_limit.attempts_time'   => 900,
    'user.login_rate_limit.lock_time'       => 900,
    'agent.login_rate_limit.enabled'        => true,
    'agent.login_rate_limit.attempts'       => 20,
    'agent.login_rate_limit.attempts_time'  => 900,
    'agent.login_rate_limit.lock_time'      => 900,

    ####################################################################################################################
    # user_style
    ####################################################################################################################

    'user_style.bg_color'                        => '#FFFFFF',
    'user_style.dark_well_bg_color'              => '#EEEEEE',
    'user_style.light_well_bg_color'             => '#FFFFFF',
    'user_style.text_color'                      => '#333333',

    'user_style.header_bar_bg_color'             => '#2a69a9',
    'user_style.header_text_color'               => '#FFFFFF',

    'user_style.header_name_color'               => '#000000',
    'user_style.header_tagline_color'            => '#5A5A5A',

    'user_style.header_tabs_link_color'          => '#0088CC',

    'user_style.body_bg_color'                   => '#ededed',
    'user_style.content_bg_color'                => '#FFFFFF',
    'user_style.content_border_color'            => '#D2D0D0',
    'user_style.cal_date_bg'                     => '#F8F8F8',
    'user_style.cal_date_text'                   => '#888',
    'user_style.cal_date_border'                 => '#DEDEDE',
    'user_style.cal_date_month_bg'               => '#2A69A9',
    'user_style.cal_date_month_text'             => '#fff',
    'user_style.link_color'                      => '#0088CC',
    'user_style.link_color_hover'                => '#005580',
    'user_style.foot_copy_color'                 => '#B2B2B2',

    'user_style.meta_text_color'                 => '#898888',

    'user_style.big_header_color'                => '#B2B1B1',

    'user_style.btn_bg1'                         => '#ffffff',
    'user_style.btn_bg2'                         => '#e6e6e6',

    'user_style.btn_primary_bg1'                => '#0088cc',
    'user_style.btn_primary_bg2'                => '#0055cc',
);
