<?php if (!defined('DP_ROOT')) exit('No access');
set_include_path(
	DP_ROOT.'/vendor-src/zend/library'
	.PATH_SEPARATOR.
	DP_ROOT.'/vendor-src/ezcomponents'
	.PATH_SEPARATOR.
    DP_ROOT.'/vendor-src/pear/lib'
    .PATH_SEPARATOR.
	get_include_path()
);

// Composer-managed sources
require_once DP_ROOT.'/vendor/autoload.php';
require_once DP_ROOT.'/src/Orb/Util/ClassLoader.php';

$loader = new \Orb\Util\ClassLoader();

$loader->registerNamespaces(array(
	'Application'        => DP_ROOT.'/src',
	'Cloud'              => DP_ROOT.'/src',
    'Bundle'             => DP_ROOT.'/src',
	'Orb'                => DP_ROOT.'/src',

	'DpUnitTests'        => DP_ROOT.'/testing/tests/unit',
	'DpIntegrationTests' => DP_ROOT.'/testing/tests/integration',
	'DpTestingMocks'     => DP_ROOT.'/testing/src',

	'Metadata'           => DP_ROOT.'/vendor-src/metadata/src',
    'Leth'               => DP_ROOT.'/vendor-src/php-ipaddress/classes',
	'libphonenumber'     => DP_ROOT.'/vendor-src/libphonenumber/src',
	'Bdt\\Clickatell'    => DP_ROOT.'/vendor-src/guzzle-clickatell/src'
));

$loader->registerNamespaceFallbacks(array(DP_WEB_ROOT . '/plugins'));

$loader->registerPrefixes(array(
	'mPDF_'           => DP_ROOT.'/vendor-src/mpdf/lib',
	'File_'           => DP_ROOT.'/vendor-src/pear/lib',
	'PEAR_'           => DP_ROOT.'/vendor-src/pear/lib',
	'EWSType_'        => DP_ROOT.'/vendor-src/php-ews',
	'Services_Twilio' => DP_ROOT.'/vendor-src/twilio-php'
));

$loader->registerClassNames(array(
	'Akismet'                         => DP_ROOT.'/vendor-src/php5-akismet/src/main/php/net/achingbrain/Akismet.class.php',
	'Browser'                         => DP_ROOT.'/vendor-src/Browser/Browser.php',
	'CssMin'                          => DP_ROOT.'/vendor-src/cssmin/cssmin.php',
	'LightOpenID'                     => DP_ROOT.'/vendor-src/lightopenid/openid.php',
	'MimeMailParser'                  => DP_ROOT.'/vendor-src/php-mime-mail-parser/MimeMailParser.php',
	'MimeMailParser_attachment'       => DP_ROOT.'/vendor-src/php-mime-mail-parser/attachment.class.php',
	'Phirehose'                       => DP_ROOT.'/vendor-src/phirehose/Phirehose.php',
	'UserstreamPhirehose'             => DP_ROOT.'/vendor-src/phirehose/UserstreamPhirehose.php',
	'HipChatApi'                      => DP_ROOT.'/vendor-src/hipchat/HipChatApi.php',
	'Markdown_Parser'                 => DP_ROOT.'/vendor-src/php-markdown/markdown.php',
	'FineDiff'                        => DP_ROOT.'/vendor-src/PHP-FineDiff/finediff.php',
	'GoogleOpenID'                    => DP_ROOT.'/vendor-src/googleopenid/GoogleOpenID.php',
	'POParser'                        => DP_ROOT.'/vendor-src/simplepo/POParser.php',
	'TempPoMsgStore'                  => DP_ROOT.'/vendor-src/simplepo/POParser.php',
	'Emogrifier'                      => DP_ROOT.'/vendor-src/emogrifier/emogrifier.php',

	'Text_LanguageDetect'             => DP_ROOT.'/vendor-src/Text_LanguageDetect/lib/Text/LanguageDetect.php',
	'Text_LanguageDetect_Exception'   => DP_ROOT.'/vendor-src/Text_LanguageDetect/lib/Text/LanguageDetect/Exception.php',
	'Text_LanguageDetect_ISO639'      => DP_ROOT.'/vendor-src/Text_LanguageDetect/lib/Text/LanguageDetect/ISO639.php',
	'Text_LanguageDetect_Parser'      => DP_ROOT.'/vendor-src/Text_LanguageDetect/lib/Text/LanguageDetect/Parser.php',

	'EpiCurl'                         => DP_ROOT.'/vendor-src/twitter-async/EpiCurl.php',
	'EpiOAuth'                        => DP_ROOT.'/vendor-src/twitter-async/EpiOAuth.php',
	'EpiOSequence'                    => DP_ROOT.'/vendor-src/twitter-async/EpiOSequence.php',
	'EpiTwitter'                      => DP_ROOT.'/vendor-src/twitter-async/EpiTwitter.php',

	'phpthumb_ico'                    => DP_ROOT.'/vendor-src/phpthumb/phpthumb.ico.php',
	'PasswordHash'                    => DP_ROOT.'/vendor-src/phpass/PasswordHash.php',

	'EWS_Exception'                   => DP_ROOT.'/vendor-src/php-ews/EWS_Exception.php',
	'EWSAutodiscover'                 => DP_ROOT.'/vendor-src/php-ews/EWSAutodiscover.php',
	'EWSType'                         => DP_ROOT.'/vendor-src/php-ews/EWSType.php',
	'ExchangeWebServices'             => DP_ROOT.'/vendor-src/php-ews/ExchangeWebServices.php',
	'NTLMSoapClient'                  => DP_ROOT.'/vendor-src/php-ews/NTLMSoapClient.php',
	'NTLMSoapClient_Exchange'         => DP_ROOT.'/vendor-src/php-ews/NTLMSoapClient/Exchange.php',
	'tnef'                            => DP_ROOT.'/vendor-src/tnef-decoder/tnef.php',
	'PDODblibBundle'                  => DP_ROOT.'/vendor-src/ouster',
));

spl_autoload_register(function($classname) {
	if (strpos($classname, 'DeskproLanguages') !== 0) return false;

	$classpath = str_replace('DeskproLanguages\\', '', $classname);
	$classpath = str_replace('\\', DIRECTORY_SEPARATOR, $classpath);
	$path = DP_ROOT . '/languages/' . $classpath . '.php';

	require($path);
	return true;
});

spl_autoload_register(function($classname) {
	// Fallback to checking native apps
	$parts = explode('\\', $classname);
	if (count($parts) < 2) {
		return false;
	}

	static $paths = null;
	if (!$paths) {
		if (isset($GLOBALS['DP_CONFIG']['app_paths'])) {
			$paths = $GLOBALS['DP_CONFIG']['app_paths'];
		} else {
			$paths = array();
		}
		$paths['default'] = DP_ROOT.'/apps';
	}

	$appname = array_shift($parts);

	foreach ($paths as $prefix => $base_path) {
		if ($prefix === 'default' || strpos($appname, $prefix) === 0) {
			$path = $base_path . '/'. $appname . '/native/' . implode('/', $parts) . '.php';
			if (file_exists($path)) {
				require_once($path);
				return true;
			}
		}
	}

	return false;
});

$loader->register();

define('QP_NO_AUTOLOADER', true);

$GLOBALS['DP_AUTOLOADER'] = $loader;

// ezC autoloading
require DP_ROOT.'/vendor-src/ezcomponents/Base/src/ezc_bootstrap.php';
spl_autoload_register(function($classname) {
	if (strpos($classname, 'ezc') !== 0) return false;
	return ezcBase::autoload($classname);
});

if (!defined('GEOIP_API_INC_PATH')) {
	define('GEOIP_API_INC_PATH', DP_ROOT.'/vendor-src/geoip-api');
}

// Needed for assetic build to work
class_exists('CssMin');

use Doctrine\Common\Annotations\AnnotationRegistry;
AnnotationRegistry::registerLoader(function($class) use ($loader) {
    $loader->loadClass($class);
    return class_exists($class, false);
});
AnnotationRegistry::registerFile(DP_ROOT.'/vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php');

require DP_ROOT.'/vendor/swiftmailer/swiftmailer/lib/swift_required.php';
\Swift_DependencyContainer::getInstance()->register('cache.disk')-> asSharedInstanceOf('Orb\\Mail\\KeyCache\\DiskKeyCache')->withDependencies(array('cache.inputstream', 'tempdir'));

require DP_ROOT.'/vendor-src/querypath/src/qp.php';
