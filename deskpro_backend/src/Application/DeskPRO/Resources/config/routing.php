<?php

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
	array('_controller' => 'DeskPRO:Blob:showBlob', 'filename' => ''),
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

return $collection;
