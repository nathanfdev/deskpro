<?php if (!defined('DP_ROOT')) exit('No access');

require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/RouteCollection.php');
require_once(DP_ROOT.'/src/Application/DeskPRO/Routing/Route.php');

use Application\DeskPRO\Routing\RouteCollection;

$collection = new RouteCollection();

$collection->create('proxy', array(
    'path'        => '/proxy/{key}',
    'controller'  => 'DeskPRO:Widget:proxy',
));

$collection->create('serve_file_root', array(
    'path'        => '/file.php',
    'controller'  => '(see: serve_file.php)',
));

$collection->create('serve_blob', array(
    'path'        => '/file.php/{blob_auth_id}/{filename}',
    'controller'  => '(see: serve_file.php)',
));

$collection->create('serve_dp_asset', array(
    'path'        => '/file.php/dp-asset/{filename}',
    'controller'  => '(see: serve_file.php)',
));

$collection->create('serve_blob_size', array(
    'path'        => '/file.php/size/{s}/{blob_auth_id}/{filename}',
    'controller'  => '(see: serve_file.php)',
));

$collection->create('serve_blob_sizefit', array(
    'path'        => '/file.php/size/{s}/size-fit/{blob_auth_id}/{filename}',
    'controller'  => '(see: serve_file.php)',
));

$collection->create('serve_blob_app_asset', array(
    'path'         => '/file.php/apps/{app_name}/{type}/{path}',
    'requirements' => array('app_name' => '[a-zA-Z0-9\-\_\.]+', 'type' => '(app|js|css|html|res)', 'path' => '.*+'),
    'controller'   => '(see: serve_file.php)',
));

$collection->create('serve_person_picture', array(
    'path'          => '/file.php/avatar/{person_id}',
    'controller'    => '(see: serve_file.php)',
    'defaults'      => array('size' => 0),
    'requirements'  => array('person_id' => '\\d+'),
));

$collection->create('serve_person_picture_size', array(
    'path'          => '/file.php/avatar/{person_id}',
    'controller'    => '(see: serve_file.php)',
    'requirements'  => array('person_id' => '\\d+', 'size' => '\\d+'),
));

$collection->create('serve_default_picture', array(
    'path'        => '/file.php/avatar/{s}/default.jpg',
    'controller'  => '(see: serve_file.php)',
    'defaults'    => array('name' => 'default_picture', 's' => '0'),
));

$collection->create('favicon', array(
    'path'        => '/favicon.ico',
    'controller'  => 'DeskPRO:Blob:favicon',
));

$collection->create('serve_org_picture_default', array(
    'path'        => '/file.php/o-avatar/default',
    'controller'  => '(see: serve_file.php)',
));

$collection->create('serve_org_picture', array(
    'path'          => '/file.php/o-avatar/{org_id}',
    'controller'    => '(see: serve_file.php)',
    'requirements'  => array('person_id' => '\\d+'),
));

$collection->create('sys_log_js_error', array(
    'path'        => '/dp/log-js-error.json',
    'controller'  => 'DeskPRO:Data:logJsError',
));

$collection->create('sys_report_error', array(
    'path'        => '/dp/report-error.json',
    'controller'  => 'DeskPRO:Data:sendErrorReport',
));

$collection->create('sys_go_billing', array(
    'path'        => '/billing',
    'controller'  => 'DeskPRO:Misc:goToBilling',
));

$collection->create('data_interface_data', array(
    'path'          => '/data/interface-data.{_format}',
    'controller'    => 'DeskPRO:Data:interfaceData',
    'defaults'      => array('_format' => 'js'),
    'requirements'  => array('_format' => 'js'),
));

$collection->create('dp3_redirect_files_php', array(
    'path'        => '/files.php',
    'controller'  => 'DeskPRO:Deskpro3Redirect:downloadCat',
));

$collection->create('dp3_redirect_attachment_files_php', array(
    'path'        => '/attachment_files.php',
    'controller'  => 'DeskPRO:Deskpro3Redirect:downloadView',
));

$collection->create('dp3_redirect_ideas_php', array(
    'path'        => '/ideas.php',
    'controller'  => 'DeskPRO:Deskpro3Redirect:feedback',
));

$collection->create('dp3_redirect_kb_article_php', array(
    'path'        => '/kb_article.php',
    'controller'  => 'DeskPRO:Deskpro3Redirect:articleView',
));

$collection->create('dp3_redirect_kb_cat_php', array(
    'path'        => '/kb_cat.php',
    'controller'  => 'DeskPRO:Deskpro3Redirect:articleCat',
));

$collection->create('dp3_redirect_kb_php', array(
    'path'        => '/kb.php',
    'controller'  => 'DeskPRO:Deskpro3Redirect:articlesHome',
));

$collection->create('dp3_redirect_login_php', array(
    'path'        => '/login.php',
    'controller'  => 'DeskPRO:Deskpro3Redirect:login',
));

$collection->create('dp3_redirect_manual_php', array(
    'path'        => '/manual.php',
    'controller'  => 'DeskPRO:Deskpro3Redirect:manuals',
));

$collection->create('dp3_redirect_manual_rewritten', array(
    'path'        => '/manual/{manual_bit}/{page_bit}',
    'controller'  => 'DeskPRO:Deskpro3Redirect:rewrittenManuals',
    'defaults'    => array('page_bit' => ''),
));

$collection->create('dp3_redirect_manual_download_php', array(
    'path'        => '/manual_download.php',
    'controller'  => 'DeskPRO:Deskpro3Redirect:manuals',
));

$collection->create('dp3_redirect_news_archive_php', array(
    'path'        => '/news_archive.php',
    'controller'  => 'DeskPRO:Deskpro3Redirect:newsArchive',
));

$collection->create('dp3_redirect_news_full_php', array(
    'path'        => '/news_full.php',
    'controller'  => 'DeskPRO:Deskpro3Redirect:newsView',
));

$collection->create('dp3_redirect_news_php', array(
    'path'        => '/news.php',
    'controller'  => 'DeskPRO:Deskpro3Redirect:newsView',
));

$collection->create('dp3_redirect_newticket_php', array(
    'path'        => '/newticket.php',
    'controller'  => 'DeskPRO:Deskpro3Redirect:newTicket',
));

$collection->create('dp3_redirect_profile_email_php', array(
    'path'        => '/profile_email.php',
    'controller'  => 'DeskPRO:Deskpro3Redirect:profile',
));

$collection->create('dp3_redirect_profile_password_php', array(
    'path'        => '/profile_password.php',
    'controller'  => 'DeskPRO:Deskpro3Redirect:profile',
));

$collection->create('dp3_redirect_profile_php', array(
    'path'        => '/profile.php',
    'controller'  => 'DeskPRO:Deskpro3Redirect:profile',
));

$collection->create('dp3_redirect_register_php', array(
    'path'        => '/register.php',
    'controller'  => 'DeskPRO:Deskpro3Redirect:register',
));

$collection->create('dp3_redirect_reset_php', array(
    'path'        => '/reset.php',
    'controller'  => 'DeskPRO:Deskpro3Redirect:login',
));

$collection->create('dp3_redirect_ticketlist_php', array(
    'path'        => '/ticketlist.php',
    'controller'  => 'DeskPRO:Deskpro3Redirect:ticketList',
));

$collection->create('dp3_redirect_ticketlist_company_php', array(
    'path'        => '/ticketlist_company.php',
    'controller'  => 'DeskPRO:Deskpro3Redirect:ticketList',
));

$collection->create('dp3_redirect_ticketlist_participate_php', array(
    'path'        => '/ticketlist_participate.php',
    'controller'  => 'DeskPRO:Deskpro3Redirect:ticketList',
));

$collection->create('dp3_redirect_troubleshooter_php', array(
    'path'        => '/troubleshooter.php',
    'controller'  => 'DeskPRO:Deskpro3Redirect:troubles',
));

$collection->create('dp3_redirect_view_php', array(
    'path'        => '/view.php',
    'controller'  => 'DeskPRO:Deskpro3Redirect:ticketView',
));

########################################################################################################################
# Incoming Channel Endpoints
########################################################################################################################

$collection->create(
    'api_channel_incoming_sms_twilio', array(
        'path'       => '/sms.php/twilio',
        'controller' => 'DeskPRO:ChannelIncoming:twilioSms',
        'methods'    => array('POST'),
    )
);

$collection->create(
    'api_channel_facebook_incoming', array(
        'path'       => '/channel/facebook/incoming',
        'controller' => 'DeskPRO:ChannelIncoming:facebook',
        'methods'    => array('POST', 'GET'),
    )
);

return $collection;
