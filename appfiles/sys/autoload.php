<?php

require_once DP_ROOT.'/vendor/symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php';
require_once DP_ROOT.'/src/Orb/Util/ClassLoader.php';

set_include_path(
	DP_ROOT.'/vendor/zend1/library'
	.PATH_SEPARATOR.
	DP_ROOT.'/vendor/zend/library'
	.PATH_SEPARATOR.
	DP_ROOT.'/vendor/ezcomponents'
	.PATH_SEPARATOR.
	get_include_path()
);

$loader = new \Orb\Util\ClassLoader();

$loader->registerNamespaces(array(
	'Application'                => DP_ROOT.'/src',
    'Bundle'                     => DP_ROOT.'/src',
	'Orb'                        => DP_ROOT.'/src',

    'Symfony'                    => DP_ROOT.'/vendor/symfony/src',
    'Doctrine\\Common'           => DP_ROOT.'/vendor/doctrine-common/lib',
    'Doctrine\\DBAL\\Migrations' => DP_ROOT.'/vendor/doctrine-migrations/lib',
    'Doctrine\\DBAL'             => DP_ROOT.'/vendor/doctrine-dbal/lib',
    'Doctrine'                   => DP_ROOT.'/vendor/doctrine-orm/lib',
	'Gedmo'                      => DP_ROOT.'/vendor/DoctrineExtensions/lib',
    'Zend'                       => DP_ROOT.'/vendor/zend/library',
));

$loader->registerPrefixes(array(
    'Swift_'      => DP_ROOT.'/vendor/swiftmailer/lib/classes',
    'Twig_'       => DP_ROOT.'/vendor/twig/lib',
	'Pheanstalk'  => DP_ROOT.'/vendor/pheanstalk/classes',
	'Zend_'       => DP_ROOT.'/vendor/zend1/library',
));

$loader->registerClassNames(array(
	'LightOpenID'                     => DP_ROOT.'/vendor/lightopenid/openid.php',
	'Facebook'                        => DP_ROOT.'/vendor/facebook/src/facebook.php',
	'FacebookApiException'            => DP_ROOT.'/vendor/facebook/src/facebook.php',
	'MimeMailParser'                  => DP_ROOT.'/vendor/php-mime-mail-parser/MimeMailParser.php',
	'MimeMailParser_attachment'       => DP_ROOT.'/vendor/php-mime-mail-parser/attachment.class.php',
	'Phirehose'                       => DP_ROOT.'/vendor/phirehose/Phirehose.php',
	'UserstreamPhirehose'             => DP_ROOT.'/vendor/phirehose/UserstreamPhirehose.php',
	'Markdown_Parser'                 => DP_ROOT.'/vendor/php-markdown/markdown.php',
));

$loader->register();

// ezC autoloading
require DP_ROOT.'/vendor/ezcomponents/Base/src/ezc_bootstrap.php';
spl_autoload_register(array('ezcBase', 'autoload'), true, true);