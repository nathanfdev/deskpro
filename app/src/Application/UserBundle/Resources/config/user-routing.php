<?php if (!defined('DP_ROOT')) exit('No access');

require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/RouteCollection.php');
require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/Route.php');

use Application\DeskPRO\Routing\RouteCollection;

$collection = new RouteCollection();

$collection->create('user', array(
	'path'        => '/',
	'controller'  => 'UserBundle:Portal:portal',
	'defaults'    => array('_locale' => 'en'),
));

$collection->create('user_admin_rendertpl', array(
	'path'        => '/admin-render-template/{type}',
	'controller'  => 'UserBundle:Main:adminRenderTemplate',
));

$collection->create('user_comment_form_login_partial', array(
	'path'        => '/_misc/comment-form-login-partial',
	'controller'  => 'UserBundle:Main:commentFormLoginPartial',
));

$collection->create('user_test', array(
	'path'        => '/test',
	'controller'  => 'UserBundle:Test:index',
));

$collection->create('user_saverating', array(
	'path'        => '/portal/save-rating/{object_type}/{object_id}',
	'controller'  => 'UserBundle:Portal:saveRating',
	'methods'     => array('POST'),
));

$collection->create('user_newcomment_finishlogin', array(
	'path'        => '/portal/save-comment/login-finished/{comment_type}/{comment_id}',
	'controller'  => 'UserBundle:Portal:newCommentFinishLogin',
));

$collection->create('user_accept_upload', array(
	'path'        => '/accept-temp-upload',
	'controller'  => 'UserBundle:Main:acceptTempUpload',
));

$collection->create('user_validate_email', array(
	'path'        => '/validate-email/{id}/{auth}',
	'controller'  => 'UserBundle:Main:validateEmail',
));

$collection->create('user_validate_ticket', array(
	'path'        => '/validate-ticket-email/{access_code}',
	'controller'  => 'UserBundle:Main:validateTicketEmail',
));

$collection->create('user_jstell_login', array(
	'path'        => '/login/jstell/{jstell}/{security_token}/{usersource_id}',
	'controller'  => 'UserBundle:Login:jstellLogin',
));

$collection->create('user_login', array(
	'path'        => '/login',
	'controller'  => 'UserBundle:Login:index',
));

$collection->create('user_login_inline', array(
	'path'        => '/login/inline-login',
	'controller'  => 'UserBundle:Login:inlineLogin',
));

$collection->create('user_login_usersource_sso', array(
	'path'          => '/login/usersource-sso/{usersource_id}',
	'controller'    => 'UserBundle:Login:usersourceSso',
	'requirements'  => array('usersource_id' => '\\d+'),
));

$collection->create('user_logout', array(
	'path'        => '/logout/{auth}',
	'controller'  => 'UserBundle:Login:logout',
));

$collection->create('user_saml_sls', array(
	'path'        => '/saml/sls/{usersource_id}',
	'controller'  => 'UserBundle:Login:samlSingleLogoutService',
));

$collection->create('user_login_authenticate_local', array(
	'path'        => '/login/authenticate-password',
	'controller'  => 'UserBundle:Login:authenticateLocal',
	'defaults'    => array('usersource_id' => 0),
));

$collection->create('user_login_authenticate', array(
	'path'          => '/login/authenticate/{usersource_id}',
	'controller'    => 'UserBundle:Login:authenticate',
	'defaults'      => array('usersource_id' => 0),
	'requirements'  => array('usersource_id' => '\\d+'),
));

$collection->create('user_login_callback', array(
	'path'          => '/login/authenticate-callback/{usersource_id}',
	'controller'    => 'UserBundle:Login:authenticateCallback',
	'requirements'  => array('usersource_id' => '\\d+'),
));

$collection->create('user_login_resetpass', array(
	'path'        => '/login/reset-password',
	'controller'  => 'UserBundle:Login:resetPassword',
));

$collection->create('user_login_resetpass_send', array(
	'path'        => '/login/reset-password/send.{_format}',
	'controller'  => 'UserBundle:Login:sendResetPassword',
	'defaults'    => array('_format' => 'html'),
));

$collection->create('user_login_resetpass_newpass', array(
	'path'          => '/login/reset-password/{code}',
	'controller'    => 'UserBundle:Login:resetPasswordNewPass',
	'requirements'  => array('code' => '[A-Za-z0-9\\-]{17,}'),
));

$collection->create('user_login_agentlogin', array(
	'path'        => '/login/agent-login/{code}',
	'controller'  => 'UserBundle:Login:authAgentLogin',
));

$collection->create('user_register', array(
	'path'        => '/register',
	'controller'  => 'UserBundle:Register:register',
));

$collection->create('user_profile', array(
	'path'        => '/profile',
	'controller'  => 'UserBundle:Profile:index',
));

$collection->create('user_profile_setlang', array(
	'path'        => '/profile/quick-set-language',
	'controller'  => 'UserBundle:Main:quickSetLanguage',
));

$collection->create('user_profile_associate_twitter', array(
	'path'        => '/profile/associate-twitter',
	'controller'  => 'UserBundle:Profile:associateTwitter',
));

$collection->create('user_profile_twitter_remove', array(
	'path'          => '/profile/twitter/{account_id}/remove',
	'controller'    => 'UserBundle:Profile:removeTwitter',
	'requirements'  => array('account_id' => '\\d+'),
));

$collection->create('user_profile_changepassword', array(
	'path'        => '/profile/change-password',
	'controller'  => 'UserBundle:Profile:changePassword',
	'methods'     => array('POST'),
));

$collection->create('user_profile_emails_new', array(
	'path'        => '/profile/emails/new',
	'controller'  => 'UserBundle:Profile:newEmail',
	'methods'     => array('POST'),
));

$collection->create('user_profile_emails_remove', array(
	'path'        => '/profile/emails/{email_id}/remove',
	'controller'  => 'UserBundle:Profile:removeEmail',
));

$collection->create('user_profile_emails_validate_remove', array(
	'path'        => '/profile/emails/{email_id}/remove-validating',
	'controller'  => 'UserBundle:Profile:removeEmailValidating',
));

$collection->create('user_profile_emails_validate_sendlink', array(
	'path'        => '/profile/emails/{email_id}/validate/send-link',
	'controller'  => 'UserBundle:Profile:sendValidateEmailLink',
));

$collection->create('user_profile_emails_setdefault', array(
	'path'        => '/profile/emails/{email_id}/set-default',
	'controller'  => 'UserBundle:Profile:setDefaultEmail',
));

$collection->create('user_search', array(
	'path'        => '/search',
	'controller'  => 'UserBundle:Search:search',
));

$collection->create('user_search_labels', array(
	'path'          => '/search/labels/{type}/{label}',
	'controller'    => 'UserBundle:Search:labelSearch',
	'defaults'      => array('label' => '', 'type' => 'all'),
	'requirements'  => array('label' => '.*'),
));

$collection->create('user_search_omnisearch', array(
	'path'          => '/search/omnisearch/{query}',
	'controller'    => 'UserBundle:Search:omnisearch',
	'requirements'  => array('query' => '.+'),
));

$collection->create('user_search_similarto', array(
	'path'        => '/search/similar-to/{content_type}',
	'controller'  => 'UserBundle:Search:similarTo',
));

$collection->create('user_tickets', array(
	'path'        => '/tickets',
	'controller'  => 'UserBundle:Tickets:list',
));

$collection->create('user_tickets_organization', array(
	'path'        => '/tickets/organization',
	'controller'  => 'UserBundle:Tickets:listOrganization',
));

$collection->create('user_tickets_new', array(
	'path'        => '/new-ticket/{for_department_id}',
	'controller'  => 'UserBundle:NewTicket:new',
	'defaults'    => array('format' => 'normal', 'for_department_id' => 0),
));

$collection->create('user_tickets_new_finishlogin', array(
	'path'        => '/new-ticket/login-finish/{ticket_id}',
	'controller'  => 'UserBundle:NewTicket:newFinishLogin',
));

$collection->create('user_tickets_new_simple', array(
	'path'        => '/tickets/new-simple/{for_department_id}',
	'controller'  => 'UserBundle:NewTicket:new',
	'defaults'    => array('format' => 'iframe', 'for_department_id' => 0),
));

$collection->create('user_tickets_new_savestatus', array(
	'path'        => '/tickets/new/save-status',
	'controller'  => 'UserBundle:NewTicket:saveStatus',
));

$collection->create('user_tickets_new_contentsolved_save', array(
	'path'        => '/tickets/new/content-solved-save.json',
	'controller'  => 'UserBundle:NewTicket:contentSolvedSave',
));

$collection->create('user_tickets_new_contentsolved', array(
	'path'        => '/tickets/new/content-solved-redirect',
	'controller'  => 'UserBundle:NewTicket:contentSolvedRedirect',
));

$collection->create('user_tickets_new_thanks', array(
	'path'        => '/tickets/new/thanks/{ticket_ref}',
	'controller'  => 'UserBundle:NewTicket:thanks',
));

$collection->create('user_tickets_new_thanks_simple', array(
	'path'        => '/tickets/new/thanks-simple/{ticket_ref}',
	'controller'  => 'UserBundle:NewTicket:simpleThanks',
));

$collection->create('user_tickets_view', array(
	'path'        => '/ticket/{ticket_ref}',
	'controller'  => 'UserBundle:TicketView:load',
));

$collection->create('user_tickets_addreply', array(
	'path'        => '/ticket-edit/{ticket_ref}/add-reply',
	'controller'  => 'UserBundle:Tickets:addReply',
	'methods'     => array('POST'),
));

$collection->create('user_tickets_participants', array(
	'path'        => '/ticket-edit/{ticket_ref}/people',
	'controller'  => 'UserBundle:Tickets:manageParticipants',
));

$collection->create('user_tickets_participants_add', array(
	'path'        => '/ticket-edit/{ticket_ref}/people/add',
	'controller'  => 'UserBundle:Tickets:addParticipant',
	'methods'     => array('POST'),
));

$collection->create('user_tickets_participants_remove', array(
	'path'        => '/ticket-edit/{ticket_ref}/people/remove/{person_id}',
	'controller'  => 'UserBundle:Tickets:removeParticipant',
));

$collection->create('user_tickets_resolve', array(
	'path'        => '/ticket-edit/{ticket_ref}/resolve',
	'controller'  => 'UserBundle:Tickets:resolve',
));

$collection->create('user_tickets_unresolve', array(
	'path'        => '/ticket-edit/{ticket_ref}/unresolve',
	'controller'  => 'UserBundle:Tickets:unresolve',
));

$collection->create('user_tickets_feedback', array(
	'path'        => '/ticket-rate/{ticket_ref}/{auth}/{message_id}',
	'controller'  => 'UserBundle:Tickets:feedback',
));

$collection->create('user_tickets_feedback_save', array(
	'path'        => '/ticket-rate/{ticket_ref}/{auth}/{message_id}/save',
	'controller'  => 'UserBundle:Tickets:feedbackSave',
	'methods'     => array('POST'),
));

$collection->create('user_tickets_feedback_closeticket', array(
	'path'        => '/ticket-edit/{ticket_ref}/feedback/{message_id}/close-ticket',
	'controller'  => 'UserBundle:Tickets:feedbackCloseTicket',
));

$collection->create('user_articles_home', array(
	'path'        => '/kb',
	'controller'  => 'UserBundle:Articles:browse',
	'defaults'    => array('slug' => ''),
));

$collection->create('user_articles', array(
	'path'          => '/kb/{slug}',
	'controller'    => 'UserBundle:Articles:browse',
	'defaults'      => array('slug' => ''),
	'requirements'  => array('slug' => '(\\d+(\\-.*?)?)?'),
));

$collection->create('user_articles_article', array(
	'path'        => '/kb/articles/{slug}',
	'controller'  => 'UserBundle:Articles:article',
));

$collection->create('user_articles_article_togglesub', array(
	'path'        => '/kb/article-subscription/{article_id}/{auth}',
	'controller'  => 'UserBundle:Articles:articleSubscription',
));

$collection->create('user_articles_cat_togglesub', array(
	'path'        => '/kb/category-subscription/{category_id}/{auth}',
	'controller'  => 'UserBundle:Articles:categorySubscription',
));

$collection->create('user_articles_unsub_all', array(
	'path'        => '/kb/unsubscribe-all/{person_id}/{auth}',
	'controller'  => 'UserBundle:Articles:unsubscribeAll',
));

$collection->create('user_articles_article_agent_iframe', array(
	'path'          => '/kb/articles/agent-iframe/{article_id}/{agent_session_id}',
	'controller'    => 'UserBundle:Articles:articleAgentIframe',
	'requirements'  => array('article_id' => '\\d+'),
));

$collection->create('user_articles_newcomment', array(
	'path'        => '/kb/new-comment/{article_id}',
	'controller'  => 'UserBundle:Articles:newComment',
	'methods'     => array('POST'),
));

$collection->create('user_downloads_home', array(
	'path'        => '/downloads',
	'controller'  => 'UserBundle:Downloads:browse',
	'defaults'    => array('slug' => ''),
));

$collection->create('user_downloads', array(
	'path'          => '/downloads/{slug}',
	'controller'    => 'UserBundle:Downloads:browse',
	'defaults'      => array('slug' => ''),
	'requirements'  => array('slug' => '(\\d+(\\-.*?)?)?'),
));

$collection->create('user_downloads_file', array(
	'path'        => '/downloads/files/{slug}',
	'controller'  => 'UserBundle:Downloads:file',
));

$collection->create('user_downloads_file_download', array(
	'path'        => '/downloads/files/{slug}/download',
	'controller'  => 'UserBundle:Downloads:downloadFile',
));

$collection->create('user_downloads_newcomment', array(
	'path'        => '/downloads/new-comment/{download_id}',
	'controller'  => 'UserBundle:Downloads:newComment',
	'methods'     => array('POST'),
));

$collection->create('user_news_home', array(
	'path'          => '/news.{_format}',
	'controller'    => 'UserBundle:News:browse',
	'defaults'      => array('slug' => '', 'page' => 1, 		'_format' => 'html'),
	'requirements'  => array('_format' => '(html|rss)'),
));

$collection->create('user_news', array(
	'path'          => '/news/{slug}.{_format}',
	'controller'    => 'UserBundle:News:browse',
	'defaults'      => array('slug' => '', 'page' => 1, 		'_format' => 'html'),
	'requirements'  => array(
		'slug'     => '(\\d+(\\-.*?)?)?',
		'page'     => '\\d+',
		'_format'  => '(html|rss)',
	),
));

$collection->create('user_news_view', array(
	'path'        => '/news/view/{slug}',
	'controller'  => 'UserBundle:News:view',
));

$collection->create('user_news_newcomment', array(
	'path'        => '/news/new-comment/{post_id}',
	'controller'  => 'UserBundle:News:newComment',
	'methods'     => array('POST'),
));

$collection->create('user_feedback_home', array(
	'path'        => '/feedback',
	'controller'  => 'UserBundle:Feedback:filter',
	'defaults'    => array(
		'status'    => 'open',
		'slug'      => 'all-categories',
		'order_by'  => 'popular',
	),
));

$collection->create('user_feedback', array(
	'path'          => '/feedback/{order_by}/{status}/{slug}',
	'controller'    => 'UserBundle:Feedback:filter',
	'defaults'      => array(
		'status'    => 'open',
		'slug'      => 'all-categories',
		'order_by'  => 'popular',
	),
	'requirements'  => array(
		'slug'      => '((\\d+(\\-.*?)?)?)|all\\-categories',
		'status'    => '(open|any-status|gathering\\-feedback|active|closed)(\\.([0-9]+))?',
		'order_by'  => '(popular|newest|most\\-voted|i\\-voted)',
	),
));

$collection->create('user_feedback_new', array(
	'path'        => '/feedback/new-feedback',
	'controller'  => 'UserBundle:Feedback:filter',
	'defaults'    => array(
		'just_form'  => 1,
		'status'     => 'any-status',
		'slug'       => 'all-categories',
		'order_by'   => 'popular',
	),
));

$collection->create('user_feedback_view', array(
	'path'        => '/feedback/view/{slug}',
	'controller'  => 'UserBundle:Feedback:view',
));

$collection->create('user_feedback_newfeedback_finishlogin', array(
	'path'        => '/feedback/new-feedback/login-finished/{feedback_id}',
	'controller'  => 'UserBundle:Feedback:newFinishLogin',
));

$collection->create('user_feedback_newcomment', array(
	'path'        => '/feedback/new-comment/{feedback_id}',
	'controller'  => 'UserBundle:Feedback:newComment',
	'methods'     => array('POST'),
));

$collection->create('user_feedback_vote', array(
	'path'        => '/feedback/vote/{feedback_id}',
	'controller'  => 'UserBundle:Feedback:vote',
	'methods'     => array('POST'),
));

$collection->create('user_chat_initsession', array(
	'path'        => '/chat/chat-session',
	'controller'  => 'UserBundle:Chat:chatSession',
));

$collection->create('user_chat_widgetisavail', array(
	'path'        => '/dp.php/chat/is-available.js',
	'controller'  => '(see: serve_dp.php)',
));

$collection->create('user_chat_poll', array(
	'path'        => '/chat/poll/{session_code}',
	'controller'  => 'UserBundle:Chat:poll',
));

$collection->create('user_chat_sendmessage', array(
	'path'        => '/chat/send-message/{session_code}',
	'controller'  => 'UserBundle:Chat:sendMessage',
));

$collection->create('user_chat_sendmessage_attach', array(
	'path'        => '/chat/send-attach/{session_code}',
	'controller'  => 'UserBundle:Chat:sendFile',
));

$collection->create('user_chat_sendusertyping', array(
	'path'        => '/chat/user-typing/{session_code}',
	'controller'  => 'UserBundle:Chat:userTyping',
));

$collection->create('user_chat_chatended', array(
	'path'        => '/chat/chat-finished/{session_code}',
	'controller'  => 'UserBundle:Chat:chatEnded',
));

$collection->create('user_chat_chatended_feedback', array(
	'path'        => '/chat/chat-finished-feedback/{session_code}',
	'controller'  => 'UserBundle:Chat:chatEndedFeedback',
));

$collection->create('user_chatlogs', array(
	'path'        => '/chat-logs',
	'controller'  => 'UserBundle:ChatLog:list',
));

$collection->create('user_chatlogs_view', array(
	'path'          => '/chat-logs/{conversation_id}',
	'controller'    => 'UserBundle:ChatLog:view',
	'requirements'  => array('conversation_id' => '\\d+'),
));

$collection->create('user_widget_overlay', array(
	'path'        => '/widget/overlay.html',
	'controller'  => 'UserBundle:Widget:overlay',
));

$collection->create('user_widget_newticket', array(
	'path'        => '/widget/new-ticket.json',
	'controller'  => 'UserBundle:Widget:newTicket',
));

$collection->create('user_widget_newfeedback', array(
	'path'        => '/widget/new-feedback.json',
	'controller'  => 'UserBundle:Widget:newFeedback',
));

$collection->create('user_widget_chat', array(
	'path'        => '/widget/chat.html',
	'controller'  => 'UserBundle:Widget:chat',
));

$collection->create('user_long_tweet_view', array(
	'path'          => '/long-tweet/{long_id}',
	'controller'    => 'UserBundle:Twitter:viewLong',
	'requirements'  => array('long_id' => '\\d+'),
));

return $collection;
