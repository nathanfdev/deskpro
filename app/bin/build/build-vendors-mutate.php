#!/usr/bin/env php
<?php
if (php_sapi_name() != 'cli') {
    echo "This script must only be run from the CLI.\n";
    echo "Contact support@deskpro.com if you require assistance.\n";
    exit(1);
}

define('DP_BUILDING', true);
define('DP_ROOT', realpath(__DIR__.'/../../'));
define('DP_WEB_ROOT', realpath(__DIR__.'/../../../'));
define('DP_CONFIG_FILE', DP_WEB_ROOT.'/config.php');

require DP_ROOT.'/bin/build/inc.php';
require DP_ROOT.'/sys/system.php';

/**
 * This script makes various modifications to vendor files as required.
 */
class VendorMutate
{
    public function mutateGeoipApi()
    {
        $path = DP_ROOT.'/vendor-src/geoip-api/geoipcity.inc';
        $file = file_get_contents($path);

        $file = str_replace("require_once 'geoip.inc';", "require_once DP_ROOT.'/vendor-src/geoip-api/geoip.inc';", $file);
        $file = str_replace("require_once 'geoipregionvars.php';", "require_once DP_ROOT.'/vendor-src/geoip-api/geoipregionvars.php';", $file);

        file_put_contents($path, $file);
    }
}

$mutate = new VendorMutate();
$mutate->mutateGeoipApi();
