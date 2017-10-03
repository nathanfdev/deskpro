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

namespace DpSys\SoftwareRequirements;

class DeskproRequirements extends RequirementCollection
{
    const REQUIRED_PHP_VERSION = '5.5.0';

    /**
     * Constructor that initializes the requirements.
     */
    public function __construct()
    {
        /* mandatory requirements follow */

        $installedPhpVersion = phpversion();

        $this->addRequirement(
            version_compare($installedPhpVersion, self::REQUIRED_PHP_VERSION, '>='),
            sprintf('PHP version must be at least %s (%s installed)', self::REQUIRED_PHP_VERSION, $installedPhpVersion),
            sprintf('You are running PHP version "<strong>%s</strong>", but DeskPRO needs at least PHP "<strong>%s</strong>" to run.
                Before using DeskPRO, upgrade your PHP installation, preferably to the latest version.',
                $installedPhpVersion, self::REQUIRED_PHP_VERSION),
            sprintf('Install PHP %s or newer (installed version is %s)', self::REQUIRED_PHP_VERSION, $installedPhpVersion)
        );

        $this->addPhpIniRequirement(
            'date.timezone', true, false,
            'date.timezone setting must be set',
            'Set the "<strong>date.timezone</strong>" setting in php.ini<a href="#phpini">*</a> (like Europe/Paris).'
        );

        if (version_compare($installedPhpVersion, self::REQUIRED_PHP_VERSION, '>=')) {
            $timezones = [];
            foreach (\DateTimeZone::listAbbreviations() as $abbreviations) {
                foreach ($abbreviations as $abbreviation) {
                    $timezones[$abbreviation['timezone_id']] = true;
                }
            }

            $this->addRequirement(
                isset($timezones[@date_default_timezone_get()]),
                sprintf('Configured default timezone "%s" must be supported by your installation of PHP', @date_default_timezone_get()),
                'Your default timezone is not supported by PHP. Check for typos in your <strong>php.ini</strong> file and have a look at the list of deprecated timezones at <a href="http://php.net/manual/en/timezones.others.php">http://php.net/manual/en/timezones.others.php</a>.'
            );
        }

        $this->addRequirement(
            function_exists('iconv'),
            'iconv must be installed',
            'Install and enable the <strong>iconv</strong> extension.'
        );

        $this->addRequirement(
            function_exists('json_encode'),
            'JSON must be installed',
            'Install and enable the <strong>JSON</strong> extension.'
        );

        $this->addRequirement(
            function_exists('session_start'),
            'session must be installed',
            'Install and enable the <strong>session</strong> extension.'
        );

        $this->addRequirement(
            function_exists('ctype_alpha'),
            'ctype must be installed',
            'Install and enable the <strong>ctype</strong> extension.'
        );

        $this->addRequirement(
            function_exists('token_get_all'),
            'Tokenizer must be installed',
            'Install and enable the <strong>Tokenizer</strong> extension.'
        );

        $this->addRequirement(
            function_exists('simplexml_import_dom'),
            'SimpleXML must be installed',
            'Install and enable the <strong>SimpleXML</strong> extension.'
        );

        $this->addPhpIniRequirement('detect_unicode', false);

        if (extension_loaded('suhosin')) {
            $this->addPhpIniRequirement(
                'suhosin.executor.include.whitelist',
                create_function('$cfgValue', 'return false !== stripos($cfgValue, "phar");'),
                false,
                'suhosin.executor.include.whitelist must be configured correctly in php.ini',
                'Add "<strong>phar</strong>" to <strong>suhosin.executor.include.whitelist</strong> in php.ini<a href="#phpini">*</a>.'
            );
        }

        if (extension_loaded('xdebug')) {
            $this->addPhpIniRequirement(
                'xdebug.show_exception_trace', false, true
            );

            $this->addPhpIniRequirement(
                'xdebug.scream', false, true
            );

            $this->addPhpIniRecommendation(
                'xdebug.max_nesting_level',
                create_function('$cfgValue', 'return $cfgValue > 100;'),
                true,
                'xdebug.max_nesting_level should be above 100 in php.ini',
                'Set "<strong>xdebug.max_nesting_level</strong>" to e.g. "<strong>250</strong>" in php.ini<a href="#phpini">*</a>'
            );
        }

        $pcreVersion = defined('PCRE_VERSION') ? (float) PCRE_VERSION : null;

        $this->addRequirement(
            null !== $pcreVersion,
            'PCRE must be installed',
            'Install the <strong>PCRE</strong> extension (version 8.0+).'
        );

        $this->addRequirement(
            function_exists('curl_init'),
            'cURL must be installed.',
            'Install and enable the <strong>cURL</strong> extension.'
        );

        if (extension_loaded('mbstring')) {
            $this->addPhpIniRequirement(
                'mbstring.func_overload',
                create_function('$cfgValue', 'return (int) $cfgValue === 0;'),
                true,
                'string functions should not be overloaded',
                'Set "<strong>mbstring.func_overload</strong>" to <strong>0</strong> in php.ini<a href="#phpini">*</a> to disable function overloading by the mbstring extension.'
            );
        }

        $this->addRequirement(
            function_exists('gzopen'),
            'zlib must be installed',
            'Install and enable the <strong>zlib</strong> extension.'
        );

        if (null !== $pcreVersion) {
            $this->addRecommendation(
                $pcreVersion >= 8.0,
                sprintf('PCRE extension should be at least version 8.0 (%s installed)', $pcreVersion),
                '<strong>PCRE 8.0+</strong> is preconfigured in PHP but you are using an outdated version of it. DeskPRO probably works anyway but it is recommended to upgrade your PCRE extension.'
            );
        }

        $this->addRequirement(
            class_exists('DomDocument'),
            'PHP-DOM and PHP-XML modules must be installed',
            'Install and enable the <strong>PHP-DOM</strong> and the <strong>PHP-XML</strong> modules.'
        );

        $this->addRequirement(
            function_exists('mb_strlen'),
            'mbstring must be installed',
            'Install and enable the <strong>mbstring</strong> extension.'
        );

        $this->addRequirement(
            function_exists('iconv'),
            'iconv must be installed',
            'Install and enable the <strong>iconv</strong> extension.'
        );

        $this->addRequirement(
            function_exists('utf8_decode'),
            'XML must be installed',
            'Install and enable the <strong>XML</strong> extension.'
        );

        $this->addRequirement(
            function_exists('filter_var'),
            'filter must be installed',
            'Install and enable the <strong>filter</strong> extension.'
        );

        $this->addRecommendation(
            function_exists('ldap_connect'),
            'LDAP should be be installed',
            'Install and enable the <strong>LDAP</strong> extension if you want to use LDAP or Active Directory integrations.'
        );

        if (function_exists('ldap_connect')) {
            $this->addPhpIniRecommendation(
                'ldap_max_limit',
                function ($ldap_conn_limit) {
                    if ($ldap_conn_limit != '-1' && (int) $ldap_conn_limit < 5) {
                        return false;
                    }

                    return true;
                },
                true,
                'ldap_max_limit must be >= 5',
                'We recommend changing the ldap.max_links setting to "-1" or to a value above 5.'
            );
        }

        $this->addRecommendation(
            function_exists('imap_open'),
            'IMAP should be installed',
            'Install and enable the <strong>IMAP</strong> extension. This is required if you want to read email from IMAP email servers.'
        );

        $this->addRecommendation(
            extension_loaded('soap'),
            'SoapClient should be installed.',
            'Install and enable the <strong>SOAP</strong> extension. This is required if you want to use any Microsoft Exchange services.'
        );

        $this->addRecommendation(
            function_exists('openssl_encrypt'),
            'OpenSSL should be installed ',
            'Install and enable the <strong>OpenSSL</strong> extension. This is required for secure networking (e.g., https, secure incoming and outgoing email, etc.).'
        );

        if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
            $this->addRecommendation(
                function_exists('posix_isatty'),
                'posix_isatty() should be available',
                'Install and enable the <strong>php_posix</strong> extension (used to colorize the CLI output).'
            );
        }

        $this->addRecommendation(
            extension_loaded('intl'),
            'intl should be installed',
            'Install and enable the <strong>intl</strong> extension (used for validators).'
        );

        $this->addRecommendation(
            extension_loaded('zip'),
            'zip extension should be installed',
            'Install and enable the <strong>zip</strong> extension.'
        );

        if (extension_loaded('intl')) {
            // check for compatible ICU versions (only done when you have the intl extension)
            if (defined('INTL_ICU_VERSION')) {
                $version = INTL_ICU_VERSION;
            } else {
                $reflector = new \ReflectionExtension('intl');

                ob_start();
                $reflector->info();
                $output = strip_tags(ob_get_clean());

                preg_match('/^ICU version +(?:=> )?(.*)$/m', $output, $matches);
                $version = $matches[1];
            }

            $this->addRecommendation(
                version_compare($version, '4.0', '>='),
                'intl ICU version should be at least 4+',
                'Upgrade your <strong>intl</strong> extension with a newer ICU version (4+).'
            );

            $this->addPhpIniRecommendation(
                'intl.error_level',
                create_function('$cfgValue', 'return (int) $cfgValue === 0;'),
                true,
                'intl.error_level should be 0 in php.ini',
                'Set "<strong>intl.error_level</strong>" to "<strong>0</strong>" in php.ini<a href="#phpini">*</a> to inhibit the messages when an error occurs in ICU functions.'
            );
        }

        if (php_sapi_name() !== 'cli') {
            $accelerator =
                (extension_loaded('eaccelerator') && ini_get('eaccelerator.enable'))
                ||
                (extension_loaded('apc') && ini_get('apc.enabled'))
                ||
                (extension_loaded('Zend Optimizer+') && ini_get('zend_optimizerplus.enable'))
                ||
                (extension_loaded('Zend OPcache') && ini_get('opcache.enable'))
                ||
                (extension_loaded('xcache') && ini_get('xcache.cacher'))
                ||
                (extension_loaded('wincache') && ini_get('wincache.ocenabled'));

            $this->addRecommendation(
                $accelerator,
                'a PHP accelerator should be installed',
                'Install and/or enable a <strong>PHP accelerator</strong> (highly recommended).'
            );
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->addRecommendation(
                $this->getRealpathCacheSize() > 1000,
                'realpath_cache_size should be above 1024 in php.ini',
                'Set "<strong>realpath_cache_size</strong>" to e.g. "<strong>1024</strong>" in php.ini<a href="#phpini">*</a> to improve performance on windows.'
            );
        }

        $this->addPhpIniRecommendation('short_open_tag', false);

        $this->addPhpIniRecommendation('magic_quotes_gpc', false, true);

        $this->addPhpIniRecommendation('register_globals', false, true);

        $this->addPhpIniRecommendation('session.auto_start', false);

        $this->addRequirement(
            class_exists('PDO'),
            'PDO should be installed',
            'Install <strong>PDO</strong>.'
        );

        if (class_exists('PDO')) {
            $drivers = \PDO::getAvailableDrivers();
            $this->addRequirement(
                false !== array_search('mysql', $drivers),
                sprintf('PDO should have mysql driver installed (currently available: %s)', implode(', ', $drivers) ?
                    implode(', ', $drivers) : 'none'),
                'Install <strong>PDO mysql driver</strong>.'
            );
        }

        $this->addRequirement(
            function_exists('imagecreate') && function_exists('imagetypes'),
            'imagecreate() should be available',
            'Install and enable the <strong>GD</strong> extension.'
        );

        if (function_exists('imagetypes')) {
            $imageTypes = [
                IMG_GIF => 'GIF',
                IMG_JPG => 'JPG',
                IMG_PNG => 'PNG',
            ];

            foreach ($imageTypes as $imageType => $imageTypeName) {
                $this->addRequirement(
                    imagetypes() & $imageType,
                    "GD $imageTypeName should be enabled",
                    "Install and enable the <strong>GD</strong> extension with <strong>$imageTypeName</strong> support."
                );
            }
        }

        $check_fn = [
            'escapeshellarg',
            'exec',
            'passthru',
            'chdir',
            'proc_open',
        ];

        foreach ($check_fn as $fn) {
            $this->addRequirement(
                !$this->isFunctionDisabled($fn),
                "$fn() must not be disabled",
                'Edit php.ini and remove the disabled_functions directive'
            );
        }

        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;
        if ($DP_ENV) {
            $this->addRequirement(
                is_writable($DP_ENV->getUserCacheDir()),
                'var/cache directory must be writable',
                'You need to make your cache directory writable: <strong>'.$DP_ENV->getUserCacheDir().'</strong>'
            );
            $this->addRequirement(
                is_writable($DP_ENV->getUserLogsDir()),
                'var/logs directory must be writable',
                'You need to make your logs directory writable: <strong>'.$DP_ENV->getUserLogsDir().'</strong>'
            );
            $this->addRequirement(
                is_writable($DP_ENV->getUserTmpDir()),
                'var/tmp directory must be writable',
                'You need to make your tmp directory writable: <strong>'.$DP_ENV->getUserTmpDir().'</strong>'
            );
            $this->addRequirement(
                is_writable($DP_ENV->getUserDebugDir()),
                'var/debug directory must be writable',
                'You need to make your debug directory writable: <strong>'.$DP_ENV->getUserDebugDir().'</strong>'
            );
            $this->addRequirement(
                is_writable($DP_ENV->getUserFilesDir()),
                'attachments directory must be writable',
                'You need to make your attachments directory writable: <strong>'.$DP_ENV->getUserFilesDir().'</strong>'
            );

            if (is_writable($DP_ENV->getAppBaseKernelCacheDir())) {
                $failedDirs = [];
                foreach ([
                    $DP_ENV->getAppBaseKernelCacheDir(),
                    $DP_ENV->getAppBaseKernelCacheDir().DIRECTORY_SEPARATOR.$DP_ENV->getEnvId(),
                    $DP_ENV->getAppBaseKernelCacheDir().DIRECTORY_SEPARATOR.$DP_ENV->getEnvId().DIRECTORY_SEPARATOR.'annotations',
                    $DP_ENV->getAppBaseKernelCacheDir().DIRECTORY_SEPARATOR.$DP_ENV->getEnvId().DIRECTORY_SEPARATOR.'doctrine',
                    $DP_ENV->getAppBaseKernelCacheDir().DIRECTORY_SEPARATOR.$DP_ENV->getEnvId().DIRECTORY_SEPARATOR.'twig',
                    $DP_ENV->getAppBaseKernelCacheDir().DIRECTORY_SEPARATOR.$DP_ENV->getEnvId().DIRECTORY_SEPARATOR.'api_permissions',
                ] as $d) {
                    if (is_dir($d) && !is_writable($d)) {
                        $failedDirs[] = $d;
                    }
                }

                if ($failedDirs) {
                    $this->addRequirement(false, 'var/kernel_cache and all sub-directories must be writable', 'Make var/kernel_cache and all sub-dirs writable (not writable : '.implode(', ', $failedDirs).')');
                }
            }
        }
    }

    /**
     * Loads realpath_cache_size from php.ini and converts it to int.
     *
     * (e.g. 16k is converted to 16384 int)
     *
     * @return int
     */
    private function getRealpathCacheSize()
    {
        $size = strtolower(trim(ini_get('realpath_cache_size')));
        preg_match('/^([0-9]+)([gmk]*)$/', $size, $matches);

        if (empty($matches)) { // Invalid 'realpath_cache_size' value in php.ini
            return 0;
        }
        if (!$matches[2]) { // Just digital 'realpath_cache_size' value in php.ini
            return (int) $size;
        }

        $size = (int) $matches[1];
        switch ($matches[2]) {
            case 'g':
                return $size * 1024 * 1024 * 1024;
            case 'm':
                return $size * 1024 * 1024;
            case 'k':
                return $size * 1024;
            default:
                return 0;
        }
    }

    /**
     * Get an array of disabled functions.
     *
     * @return array
     */
    private function getDisabledFunctions()
    {
        static $functions = null;

        if ($functions === null) {
            $functions = [];
            $list      = @ini_get('disable_functions').','.@ini_get('suhosin.executor.func.blacklist');
            $list      = explode(',', $list);

            foreach ($list as $f) {
                $f = trim($f);
                if ($f) {
                    $f             = strtolower($f);
                    $functions[$f] = $f;
                }
            }
        }

        return $functions;
    }

    /**
     * @param string $func_name
     *
     * @return bool
     */
    private function isFunctionDisabled($func_name)
    {
        $func_name = strtolower($func_name);

        $disabled = $this->getDisabledFunctions();

        return isset($disabled[$func_name]);
    }
}
