<?php
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
	'Application'                  => DP_ROOT.'/src',
    'Bundle'                       => DP_ROOT.'/src',
	'Orb'                          => DP_ROOT.'/src',

	'Assetic'                        => DP_ROOT.'/vendor/assetic/src',
	'Symfony'                        => DP_ROOT.'/vendor/symfony/src',
    'Doctrine\\Common'               => DP_ROOT.'/vendor/doctrine-common/lib',
    'Doctrine\\DBAL\\Migrations'     => DP_ROOT.'/vendor/doctrine-migrations/lib',
    'Doctrine\\DBAL'                 => DP_ROOT.'/vendor/doctrine-dbal/lib',
    'Doctrine'                       => DP_ROOT.'/vendor/doctrine/lib',
	'Zend'                           => DP_ROOT.'/vendor/zend/library',
	'FOQ'                            => DP_ROOT.'/vendor',
	'Elao'                           => DP_ROOT.'/vendor/profiler',
	'Profiler'                       => DP_ROOT.'/vendor/profiler',
));

$loader->registerPrefixes(array(
    'Twig_'       => DP_ROOT.'/vendor/twig/lib',
	'Pheanstalk'  => DP_ROOT.'/vendor/pheanstalk/classes',
	'Zend_'       => DP_ROOT.'/vendor/zend1/library',
	'Elastica_'   => DP_ROOT.'/vendor/Elastica/lib'
));

$loader->registerClassNames(array(
	'DeskPRO\\Kernel\\Boot'              => DP_ROOT.'/sys/Kernel/Boot.php',
	'DeskPRO\\Kernel\\AbstractKernel'    => DP_ROOT.'/sys/Kernel/AbstractKernel.php',
	'DeskPRO\\Kernel\\AgentKernel'       => DP_ROOT.'/sys/Kernel/AgentKernel.php',
	'DeskPRO\\Kernel\\ReportKernel'      => DP_ROOT.'/sys/Kernel/ReportKernel.php',
	'DeskPRO\\Kernel\\UserKernel'        => DP_ROOT.'/sys/Kernel/UserKernel.php',
	'DeskPRO\\Kernel\\CliKernel'         => DP_ROOT.'/sys/Kernel/CliKernel.php',
	'DeskPRO\\Kernel\\SysKernel'         => DP_ROOT.'/sys/Kernel/SysKernel.php',
	'DeskPRO\\Kernel\\InstallKernel'     => DP_ROOT.'/sys/Kernel/InstallKernel.php',

	'CssMin'                          => DP_ROOT.'/vendor/cssmin/cssmin.php',
	'LightOpenID'                     => DP_ROOT.'/vendor/lightopenid/openid.php',
	'Facebook'                        => DP_ROOT.'/vendor/facebook/src/facebook.php',
	'FacebookApiException'            => DP_ROOT.'/vendor/facebook/src/facebook.php',
	'MimeMailParser'                  => DP_ROOT.'/vendor/php-mime-mail-parser/MimeMailParser.php',
	'MimeMailParser_attachment'       => DP_ROOT.'/vendor/php-mime-mail-parser/attachment.class.php',
	'Phirehose'                       => DP_ROOT.'/vendor/phirehose/Phirehose.php',
	'UserstreamPhirehose'             => DP_ROOT.'/vendor/phirehose/UserstreamPhirehose.php',
	'Markdown_Parser'                 => DP_ROOT.'/vendor/php-markdown/markdown.php',
	'FineDiff'                        => DP_ROOT.'/vendor/PHP-FineDiff/finediff.php',
));

spl_autoload_register(function($classname) {
	if (strpos($classname, 'DeskproLanguages') !== 0) return false;

	$classpath = str_replace('DeskproLanguages\\', '', $classname);
	$classpath = str_replace('\\', DIRECTORY_SEPARATOR, $classpath);
	$path = DP_ROOT . '/languages/' . $classpath . '.php';

	require($path);
	return true;
});

$loader->register();

$GLOBALS['DP_AUTOLOADER'] = $loader;

// ezC autoloading
require DP_ROOT.'/vendor/ezcomponents/Base/src/ezc_bootstrap.php';
spl_autoload_register(function($classname) {
	if (strpos($classname, 'ezc') !== 0) return false;
	return ezcBase::autoload($classname);
});

// Needed for assetic build to work
class_exists('CssMin');

use Doctrine\Common\Annotations\AnnotationRegistry;
AnnotationRegistry::registerLoader(function($class) use ($loader) {
    $loader->loadClass($class);
    return class_exists($class, false);
});
AnnotationRegistry::registerFile(DP_ROOT.'/vendor/doctrine/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php');

require DP_ROOT.'/vendor/swiftmailer/lib/swift_required.php';
