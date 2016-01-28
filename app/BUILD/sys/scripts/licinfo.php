<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
/**
 * DeskPRO.
 *
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 */

/*
 * Enable this script by adding a config.php line:
 *
 * $DP_CONFIG['sys_licinfo'] = 'abc';
 *
 * Where 'abc' is any auth code you want to use. Then call this page like:
 *
 * http://example.com/deskpro/index.php?_sys=licinfo&abc
 *
 * (Where the abc part is your auth code).
 */

#------------------------------
# Load config
#------------------------------

require_once DP_ROOT.'/sys/load_config.php';
dp_load_config();

$open = dp_get_config('sys_licinfo');
if (!$open) {
    exit;
} elseif ($open !== true) {
    // if its not a boolean true, then its an authcode
    if (!isset($_GET[$open])) {
        exit;
    }
}

$env   = 'prod';
$debug = false;

if (isset($DP_CONFIG['debug']['dev']) && $DP_CONFIG['debug']['dev']) {
    $env   = 'dev';
    $debug = true;
}

require DP_ROOT.'/sys/KernelBooter.php';
\DeskPRO\Kernel\KernelBooter::bootstrapLib(true);

$kernel_class = 'DeskPRO\\Kernel\\DpKernel';
define('DP_INTERFACE', 'sys');

$kernel = new $kernel_class($env, $debug, 'sys');
$kernel->boot();

/** @var $container \Application\DeskPRO\DependencyInjection\DeskproContainer */
$container = $kernel->getContainer();

#------------------------------
# Get license details
#------------------------------

header('Content-Type: text/plain');
$lic = \DeskPRO\Kernel\License::getLicense();

echo 'License ID  : '.$lic->getLicenseId();
echo "\n";
echo 'Expires     : '.$lic->getExpireDate()->format('Y-m-d H:i:s');
echo "\n";
echo 'Expire Days : '.$lic->getExpireDays();
echo "\n";

#------------------------------
# Refresh
#------------------------------

if (isset($_GET['refresh'])) {
    require_once DP_ROOT.'/src/Application/DeskPRO/LowUtil/RemoteRequest.php';
    try {
        $url    = \DeskPRO\Kernel\License::getSecureLicServer().'/api/license/renew-key';
        $result = \DeskPRO_LowUtil_RemoteRequester::create()->request($url, array(
            'license_id'   => $lic->getLicenseId(),
            'license_code' => $lic->getLicenseCode(),
        ), 'POST');

        if ($result) {
            $result = @json_decode($result, true);
        }
        if ($result && !empty($result['license_code'])) {
            $container->getDb()->update('settings', array(
                'value' => $result['license_code'],
            ), array(
                'name' => 'core.license',
            ));
            echo "Refresh request: done\n";
        } else {
            echo "Refresh request: failed\n";
        }
    } catch (\Exception $e) {
        echo "Refresh request: failed\n";
    }
    echo "\n";
}
