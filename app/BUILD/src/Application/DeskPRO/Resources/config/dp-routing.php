<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

if (!defined('DP_ROOT')) {
    exit('No access');
}

require_once DP_ROOT.'/src/Application/DeskPRO/Routing/RouteCollection.php';
require_once DP_ROOT.'/src/Application/DeskPRO/Routing/Route.php';

use Application\DeskPRO\Routing\RouteCollection;

$collection = new RouteCollection();

$col = $loader->import(DP_ROOT.'/src/Application/AdminInterfaceBundle/Resources/config/admin-interface-routing.php');
$col->addPrefix('/admin');
$collection->addCollection($col);

$col = $loader->import(
    DP_ROOT.'/src/Application/ReportsInterfaceBundle/Resources/config/reports-interface-routing.php'
);
$col->addPrefix('/reports');
$collection->addCollection($col);

$col = $loader->import(DP_ROOT.'/src/Application/InterfaceBundle/Resources/config/interface-routing.php');
$col->addPrefix('/reports');
$collection->addCollection($col);

$col = $loader->import(DP_ROOT.'/src/Application/AgentBundle/Resources/config/agent-routing.php');
$col->addPrefix('/agent');
$collection->addCollection($col);

$col = $loader->import(DP_ROOT.'/src/Application/AgentBundle/Resources/config/new-agent-routing.php');
$col->addPrefix('/new-agent');
$collection->addCollection($col);

$col = $loader->import(DP_ROOT.'/src/Application/EmailBundle/Resources/config/email-routing.php');
$col->addPrefix('/email');
$collection->addCollection($col);

$collection->create('proxy', [
    'path'       => '/proxy/{key}',
    'controller' => 'DeskPRO:Widget:proxy',
]);

$collection->create('serve_root', [
    'path'       => '/',
    'controller' => '(not a real route)',
]);

$collection->create('sys_serverinfo', [
    'path'         => '/__serverinfo/{path}',
    'controller'   => '(not a real route)',
    'requirements' => [
        'path' => '.*+',
    ],
]);

$collection->create('serve_file_root', [
    'path'       => '/file.php',
    'controller' => '(see: serve_file.php)',
]);

$collection->create('serve_blob', [
    'path'       => '/file.php/{blob_auth_id}/{filename}',
    'controller' => '(see: serve_file.php)',
]);

$collection->create('serve_dp_asset', [
    'path'       => '/file.php/dp-asset/{filename}',
    'controller' => '(see: serve_file.php)',
]);

$collection->create('serve_blob_size', [
    'path'       => '/file.php/size/{s}/{blob_auth_id}/{filename}',
    'controller' => '(see: serve_file.php)',
]);

$collection->create('serve_blob_sizefit', [
    'path'       => '/file.php/size/{s}/size-fit/{blob_auth_id}/{filename}',
    'controller' => '(see: serve_file.php)',
]);

$collection->create('serve_blob_app_asset', [
    'path'         => '/file.php/apps/{app_name}/{type}/{path}',
    'requirements' => [
        'app_name' => '[a-zA-Z0-9\-\_\.]+',
        'type'     => '(app|js|css|html|res)',
        'path'     => '.*+',
    ],
    'controller' => '(see: serve_file.php)',
]);

$collection->create('serve_person_picture', [
    'path'         => '/file.php/avatar/{person_id}',
    'controller'   => '(see: serve_file.php)',
    'defaults'     => ['size' => 0],
    'requirements' => ['person_id' => '\\d+'],
]);

$collection->create('serve_person_picture_size', [
    'path'         => '/file.php/avatar/{person_id}',
    'controller'   => '(see: serve_file.php)',
    'requirements' => [
        'person_id' => '\\d+',
        'size'      => '\\d+',
    ],
]);

$collection->create('serve_default_picture', [
    'path'       => '/file.php/avatar/{s}/default.jpg',
    'controller' => '(see: serve_file.php)',
    'defaults'   => [
        'name' => 'default_picture',
        's'    => '0',
    ],
]);

$collection->create('serve_org_picture_default', [
    'path'       => '/file.php/o-avatar/default',
    'controller' => '(see: serve_file.php)',
]);

$collection->create('serve_org_picture', [
    'path'         => '/file.php/o-avatar/{org_id}',
    'controller'   => '(see: serve_file.php)',
    'requirements' => ['person_id' => '\\d+'],
]);

$collection->create('sys_log_js_error', [
    'path'       => '/dp/log-js-error.json',
    'controller' => 'DeskPRO:Data:logJsError',
]);

$collection->create('sys_report_error', [
    'path'       => '/dp/report-error.json',
    'controller' => 'DeskPRO:Data:sendErrorReport',
]);

$collection->create('data_interface_data', [
    'path'         => '/data/interface-data.{_format}',
    'controller'   => 'DeskPRO:Data:interfaceData',
    'defaults'     => ['_format' => 'js'],
    'requirements' => ['_format' => 'js'],
]);

$collection->create('dp3_redirect_files_php', [
    'path'       => '/files.php',
    'controller' => 'PortalBundle:Deskpro3Redirect:downloadCat',
]);

$collection->create('dp3_redirect_attachment_files_php', [
    'path'       => '/attachment_files.php',
    'controller' => 'PortalBundle:Deskpro3Redirect:downloadView',
]);

$collection->create('dp3_redirect_ideas_php', [
    'path'       => '/ideas.php',
    'controller' => 'PortalBundle:Deskpro3Redirect:feedback',
]);

$collection->create('dp3_redirect_kb_article_php', [
    'path'       => '/kb_article.php',
    'controller' => 'PortalBundle:Deskpro3Redirect:articleView',
]);

$collection->create('dp3_redirect_kb_cat_php', [
    'path'       => '/kb_cat.php',
    'controller' => 'PortalBundle:Deskpro3Redirect:articleCat',
]);

$collection->create('dp3_redirect_kb_php', [
    'path'       => '/kb.php',
    'controller' => 'PortalBundle:Deskpro3Redirect:articlesHome',
]);

$collection->create('dp3_redirect_login_php', [
    'path'       => '/login.php',
    'controller' => 'PortalBundle:Deskpro3Redirect:login',
]);

$collection->create('dp3_redirect_manual_php', [
    'path'       => '/manual.php',
    'controller' => 'PortalBundle:Deskpro3Redirect:manuals',
]);

$collection->create('dp3_redirect_manual_rewritten', [
    'path'       => '/manual/{manual_bit}/{page_bit}',
    'controller' => 'PortalBundle:Deskpro3Redirect:rewrittenManuals',
    'defaults'   => ['page_bit' => ''],
]);

$collection->create('dp3_redirect_manual_download_php', [
    'path'       => '/manual_download.php',
    'controller' => 'PortalBundle:Deskpro3Redirect:manuals',
]);

$collection->create('dp3_redirect_news_archive_php', [
    'path'       => '/news_archive.php',
    'controller' => 'PortalBundle:Deskpro3Redirect:newsArchive',
]);

$collection->create('dp3_redirect_news_full_php', [
    'path'       => '/news_full.php',
    'controller' => 'PortalBundle:Deskpro3Redirect:newsView',
]);

$collection->create('dp3_redirect_news_php', [
    'path'       => '/news.php',
    'controller' => 'PortalBundle:Deskpro3Redirect:newsView',
]);

$collection->create('dp3_redirect_newticket_php', [
    'path'       => '/newticket.php',
    'controller' => 'PortalBundle:Deskpro3Redirect:newTicket',
]);

$collection->create('dp3_redirect_profile_email_php', [
    'path'       => '/profile_email.php',
    'controller' => 'PortalBundle:Deskpro3Redirect:profile',
]);

$collection->create('dp3_redirect_profile_password_php', [
    'path'       => '/profile_password.php',
    'controller' => 'PortalBundle:Deskpro3Redirect:profile',
]);

$collection->create('dp3_redirect_profile_php', [
    'path'       => '/profile.php',
    'controller' => 'PortalBundle:Deskpro3Redirect:profile',
]);

$collection->create('dp3_redirect_register_php', [
    'path'       => '/register.php',
    'controller' => 'PortalBundle:Deskpro3Redirect:register',
]);

$collection->create('dp3_redirect_reset_php', [
    'path'       => '/reset.php',
    'controller' => 'PortalBundle:Deskpro3Redirect:login',
]);

$collection->create('dp3_redirect_ticketlist_php', [
    'path'       => '/ticketlist.php',
    'controller' => 'PortalBundle:Deskpro3Redirect:ticketList',
]);

$collection->create('dp3_redirect_ticketlist_company_php', [
    'path'       => '/ticketlist_company.php',
    'controller' => 'PortalBundle:Deskpro3Redirect:ticketList',
]);

$collection->create('dp3_redirect_ticketlist_participate_php', [
    'path'       => '/ticketlist_participate.php',
    'controller' => 'PortalBundle:Deskpro3Redirect:ticketList',
]);

$collection->create('dp3_redirect_troubleshooter_php', [
    'path'       => '/troubleshooter.php',
    'controller' => 'PortalBundle:Deskpro3Redirect:troubles',
]);

$collection->create('dp3_redirect_view_php', [
    'path'       => '/view.php',
    'controller' => 'PortalBundle:Deskpro3Redirect:ticketView',
]);

//#######################################################################################################################
// Incoming Channel Endpoints
//#######################################################################################################################

$collection->create(
    'api_channel_incoming_sms_twilio', [
        'path'       => '/sms.php/twilio',
        'controller' => 'DeskPRO:ChannelIncoming:twilioSms',
        'methods'    => ['POST'],
    ]
);

$collection->create(
    'api_channel_facebook_incoming', [
        'path'       => '/channel/facebook/incoming',
        'controller' => 'DeskPRO:ChannelIncoming:facebook',
        'methods'    => ['POST', 'GET'],
    ]
);

//#######################################################################################################################
// JIRA Webhook Endpoint
//#######################################################################################################################

$collection->create(
    'jira_webhook_handle', [
        'path'       => '/jira/webhook',
        'controller' => 'DeskPRO:JIRAWebhook:handle',
        'methods'    => ['POST'],
    ]
);

return $collection;
