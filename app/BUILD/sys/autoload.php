<?php

use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;

//#######################################################################################################################
// Oldschool include paths
//#######################################################################################################################

set_include_path(
    DP_APP_DIR.'/vendor-src/zend/library'
    .PATH_SEPARATOR.
    DP_APP_DIR.'/vendor-src/ezcomponents'
    .PATH_SEPARATOR.
    DP_APP_DIR.'/vendor-src/pear/lib'
    .PATH_SEPARATOR.
    get_include_path()
);

//#######################################################################################################################
// Standard autoloader
//#######################################################################################################################

/** @var ClassLoader $loader */
$loader = require DP_APP_DIR.'/vendor/autoload.php';

AnnotationRegistry::registerLoader([$loader, 'loadClass']);

foreach ([
    'DeskPRO' => DP_APP_DIR.'/src',
    'Application' => DP_APP_DIR.'/src',
    'Cloud' => DP_APP_DIR.'/src',
    'Bundle' => DP_APP_DIR.'/src',
    'Orb' => DP_APP_DIR.'/src',
    'DpUnitTests' => DP_APP_DIR.'/testing/tests/unit',
    'DpTest' => DP_APP_DIR.'/tests/phpunit',
    'DpIntegrationTests' => DP_APP_DIR.'/testing/tests/integration',
    'DpTestingMocks' => DP_APP_DIR.'/testing/src',
    'Metadata' => DP_APP_DIR.'/vendor-src/metadata/src',
    'Leth' => DP_APP_DIR.'/vendor-src/php-ipaddress/classes',
    'libphonenumber' => DP_APP_DIR.'/vendor-src/libphonenumber/src',
    'Bdt\\Clickatell' => DP_APP_DIR.'/vendor-src/guzzle-clickatell/src',

    'mPDF_' => DP_APP_DIR.'/vendor-src/mpdf/lib',
    'File_' => DP_APP_DIR.'/vendor-src/pear/lib',
    'PEAR_' => DP_APP_DIR.'/vendor-src/pear/lib',
    'EWSType_' => DP_APP_DIR.'/vendor-src/php-ews',
    'Services_Twilio' => DP_APP_DIR.'/vendor-src/twilio-php',
] as $prefix => $dir) {
    $loader->add($prefix, $dir);
}

$loader->addClassMap([
    'DpShutdown' => DP_APP_DIR.'/sys/DpShutdown.php',

    'Akismet'                                                    => DP_APP_DIR.'/vendor-src/php5-akismet/src/main/php/net/achingbrain/Akismet.class.php',
    'Browser'                                                    => DP_APP_DIR.'/vendor-src/Browser/Browser.php',
    'CssMin'                                                     => DP_APP_DIR.'/vendor-src/cssmin/cssmin.php',
    'HipChatApi'                                                 => DP_APP_DIR.'/vendor-src/hipchat/HipChatApi.php',
    'Markdown_Parser'                                            => DP_APP_DIR.'/vendor-src/php-markdown/markdown.php',
    'FineDiff'                                                   => DP_APP_DIR.'/vendor-src/PHP-FineDiff/finediff.php',
    'GoogleOpenID'                                               => DP_APP_DIR.'/vendor-src/googleopenid/GoogleOpenID.php',
    'POParser'                                                   => DP_APP_DIR.'/vendor-src/simplepo/POParser.php',
    'TempPoMsgStore'                                             => DP_APP_DIR.'/vendor-src/simplepo/POParser.php',
    'Facebook'                                                   => DP_APP_DIR.'/vendor-src/facebook/php-sdk/src/facebook.php',
    'Text_LanguageDetect'                                        => DP_APP_DIR.'/vendor-src/Text_LanguageDetect/lib/Text/LanguageDetect.php',
    'Text_LanguageDetect_Exception'                              => DP_APP_DIR.'/vendor-src/Text_LanguageDetect/lib/Text/LanguageDetect/Exception.php',
    'Text_LanguageDetect_ISO639'                                 => DP_APP_DIR.'/vendor-src/Text_LanguageDetect/lib/Text/LanguageDetect/ISO639.php',
    'Text_LanguageDetect_Parser'                                 => DP_APP_DIR.'/vendor-src/Text_LanguageDetect/lib/Text/LanguageDetect/Parser.php',
    'EpiCurl'                                                    => DP_APP_DIR.'/vendor-src/twitter-async/EpiCurl.php',
    'EpiOAuth'                                                   => DP_APP_DIR.'/vendor-src/twitter-async/EpiOAuth.php',
    'EpiOSequence'                                               => DP_APP_DIR.'/vendor-src/twitter-async/EpiOSequence.php',
    'EpiTwitter'                                                 => DP_APP_DIR.'/vendor-src/twitter-async/EpiTwitter.php',
    'phpthumb_ico'                                               => DP_APP_DIR.'/vendor-src/phpthumb/phpthumb.ico.php',
    'PasswordHash'                                               => DP_APP_DIR.'/vendor-src/phpass/PasswordHash.php',
    'EWS_Exception'                                              => DP_APP_DIR.'/vendor-src/php-ews/EWS_Exception.php',
    'EWSAutodiscover'                                            => DP_APP_DIR.'/vendor-src/php-ews/EWSAutodiscover.php',
    'EWSType'                                                    => DP_APP_DIR.'/vendor-src/php-ews/EWSType.php',
    'ExchangeWebServices'                                        => DP_APP_DIR.'/vendor-src/php-ews/ExchangeWebServices.php',
    'NTLMSoapClient'                                             => DP_APP_DIR.'/vendor-src/php-ews/NTLMSoapClient.php',
    'NTLMSoapClient_Exchange'                                    => DP_APP_DIR.'/vendor-src/php-ews/NTLMSoapClient/Exchange.php',
    'tnef'                                                       => DP_APP_DIR.'/vendor-src/tnef-decoder/tnef.php',
    'PDODblibBundle'                                             => DP_APP_DIR.'/vendor-src/ouster',
    'Swift_Transport_Esmtp_Auth_XOAuth2Authenticator'            => DP_APP_DIR.'/vendor-src/swiftmailer/XOAuth2Authenticator.php',
    'Symfony\\Component\\Intl\\NumberFormatter\\NumberFormatter' => DP_APP_DIR.'/src/DeskPRO/Component/Intl/NumberFormatter.php',
]);

foreach ([
    'DpBehat\\' => DP_APP_DIR.'/tests/features/bootstrap/',
    'DpTestSrc\\' => DP_APP_DIR.'/tests/src/',
    'DpSys\\' => DP_APP_DIR.'/sys/',
    'DpRun\\' => DP_DIR.'/app/run/lib/DpRun',
    'DpScripts\\Agent\\' => DP_DIR.'/app/scripts/agent',
    'DpScripts\\User\\' => DP_DIR.'/app/scripts/user',
    'DeskPRO\\Services\\' => DP_APP_DIR.'/bin/tools/services/src',
    'DeskPRO\\ImporterTools\\' => DP_APP_DIR.'/modules/importer-tools/inc',
] as $prefix => $path) {
    $loader->addPsr4($prefix, $path);
}

//#######################################################################################################################
// Native apps
//#######################################################################################################################

spl_autoload_register(
    function ($classname) {
        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        // Fallback to checking native apps
        $parts = explode('\\', $classname);
        if (count($parts) < 2) {
            return false;
        }

        static $paths = null;
        if (!$paths) {
            if ($DP_ENV) {
                $paths = $DP_ENV->getConfig('paths.app_paths', []);
            } else {
                $paths = [];
            }
            $paths['default'] = DP_APP_DIR.'/apps';
        }

        $appname = array_shift($parts);

        foreach ($paths as $prefix => $base_path) {
            if ($prefix === 'default' || strpos($appname, $prefix) === 0) {
                $path = $base_path.'/'.$appname.'/native/'.implode('/', $parts).'.php';
                if (file_exists($path)) {
                    require_once $path;

                    return true;
                }
            }
        }

        return false;
    }
);

//#######################################################################################################################
// ezC
//#######################################################################################################################

require DP_APP_DIR.'/vendor-src/ezcomponents/Base/src/ezc_bootstrap.php';
spl_autoload_register(
    function ($classname) {
        if ($classname[0] !== 'e' || substr($classname, 0, 3) !== 'ezc') {
            return false;
        }

        return ezcBase::autoload($classname);
    }
);

//#######################################################################################################################
// Swiftmailer
//#######################################################################################################################

require DP_APP_DIR.'/vendor/swiftmailer/swiftmailer/lib/swift_required.php';
\Swift_DependencyContainer::getInstance()->register('cache.disk')->asSharedInstanceOf(
    'Orb\\Mail\\KeyCache\\DiskKeyCache'
)->withDependencies(['cache.inputstream', 'tempdir']);

//#######################################################################################################################
// Misc
//#######################################################################################################################

define('QP_NO_AUTOLOADER', true);
require DP_APP_DIR.'/vendor-src/querypath/src/qp.php';
$GLOBALS['DP_AUTOLOADER'] = $loader;

// Needed for assetic build to work
class_exists('CssMin');
