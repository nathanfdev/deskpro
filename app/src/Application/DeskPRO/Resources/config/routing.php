<?php if (!defined('DP_ROOT')) exit('No access');

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

$collection = new RouteCollection();

$collection->add('proxy', new Route(
	'/proxy/{key}',
	array('_controller' => 'DeskPRO:Widget:proxy'),
	array(),
	array()
));

$collection->add('serve_blob', new Route(
	'/file.php/{blob_auth_id}/{filename}',
	array('_controller' => 'DeskPRO:Blob:showBlob'),
	array(),
	array()
));

$collection->add('serve_person_picture', new Route(
	'/file.php/avatar/{person_id}',
	array('_controller' => 'DeskPRO:Blob:personPicture', 'size' => 0),
	array('person_id' => '\\d+'),
	array()
));

$collection->add('serve_person_picture_size', new Route(
	'/file.php/avatar/{person_id}',
	array('_controller' => 'DeskPRO:Blob:personPicture'),
	array('person_id' => '\\d+', 'size' => '\\d+'),
	array()
));

$collection->add('serve_trans_gif', new Route(
	'/download/pixel',
	array('_controller' => 'DeskPRO:Blob:getStaticFile', 'name' => 'pixel'),
	array(),
	array()
));

$collection->add('serve_default_picture', new Route(
	'/file.php/avatar/default',
	array('_controller' => 'DeskPRO:Blob:getStaticFile', 'name' => 'default_picture'),
	array(),
	array()
));

$collection->add('data_interface_data', new Route(
	'/data/interface-data.{_format}',
	array('_controller' => 'DeskPRO:Data:interfaceData', '_format' => 'js'),
	array('_format' => 'js'),
	array()
));

$collection->add('favicon', new Route(
	'/favicon.ico',
	array('_controller' => 'DeskPRO:Blob:favicon'),
	array(),
	array()
));

$collection->add('serve_org_picture_default', new Route(
	'/file.php/o-avatar/default',
	array('_controller' => 'DeskPRO:Blob:defaultOrgPicture'),
	array(),
	array()
));

$collection->add('serve_org_picture', new Route(
	'/file.php/o-avatar/{org_id}',
	array('_controller' => 'DeskPRO:Blob:orgPicture'),
	array('person_id' => '\\d+'),
	array()
));

$collection->add('sys_log_js_error', new Route(
	'/dp/log-js-error.json',
	array('_controller' => 'DeskPRO:Data:logJsError'),
	array(),
	array()
));

$collection->add('sys_report_error', new Route(
	'/dp/report-error.json',
	array('_controller' => 'DeskPRO:Data:sendErrorReport'),
	array(),
	array()
));

$collection->add('dp3_redirect_news', new Route(
	'/news.php',
	array('_controller' => 'DeskPRO:Deskpro3Redirect:redirectNews'),
	array(),
	array()
));

$collection->add('dp3_redirect_kb_home', new Route(
	'/kb.php',
	array('_controller' => 'DeskPRO:Deskpro3Redirect:redirectKbHome'),
	array(),
	array()
));

$collection->add('dp3_redirect_kb', new Route(
	'/kb_article.php',
	array('_controller' => 'DeskPRO:Deskpro3Redirect:redirectKb'),
	array(),
	array()
));

$collection->add('dp3_redirect_kbcat', new Route(
	'/kb_cat.php',
	array('_controller' => 'DeskPRO:Deskpro3Redirect:redirectKbCat'),
	array(),
	array()
));

$collection->add('dp3_redirect_idea', new Route(
	'/ideas.php',
	array('_controller' => 'DeskPRO:Deskpro3Redirect:redirectIdea'),
	array(),
	array()
));


return $collection;
