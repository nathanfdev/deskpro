<?php

require_once DP_ROOT.'/vendor/symfony/src/Symfony/Component/HttpFoundation/UniversalClassLoader.php';
require_once DP_ROOT.'/src/Orb/Util/ClassLoader.php';

set_include_path(
	DP_ROOT.'/vendor/zend1/library'
	.PATH_SEPARATOR.
	DP_ROOT.'/vendor/zend/library'
	.PATH_SEPARATOR.
	get_include_path()
);

$loader = new \Orb\Util\ClassLoader();

$loader->registerNamespaces(array(
	'DeskPRO'                    => DP_ROOT.'/src',
	'Application'                => DP_ROOT.'/src',
    'Bundle'                     => DP_ROOT.'/src',
	'Orb'                        => DP_ROOT.'/src',

    'Symfony'                    => DP_ROOT.'/vendor/symfony/src',
    'Doctrine\\Common'           => DP_ROOT.'/vendor/doctrine-common/lib',
    'Doctrine\\DBAL\\Migrations' => DP_ROOT.'/vendor/doctrine-migrations/lib',
    'Doctrine\\DBAL'             => DP_ROOT.'/vendor/doctrine-dbal/lib',
    'Doctrine'                   => DP_ROOT.'/vendor/doctrine-orm/lib',
    'Zend'                       => DP_ROOT.'/vendor/zend/library',
));

$loader->registerPrefixes(array(
    'Swift_'      => DP_ROOT.'/vendor/swiftmailer/lib/classes',
    'Twig_'       => DP_ROOT.'/vendor/twig/lib',
	'Pheanstalk'  => DP_ROOT.'/vendor/pheanstalk/classes',
	'Zend_'       => DP_ROOT.'/vendor/zend1/library',
));

$loader->registerClassNames(array(
	'LightOpenID'          => DP_ROOT.'/vendor/lightopenid/openid.php',
	'Facebook'             => DP_ROOT.'/vendor/facebook/src/facebook.php',
	'FacebookApiException' => DP_ROOT.'/vendor/facebook/src/facebook.php',
));

$loader->register();