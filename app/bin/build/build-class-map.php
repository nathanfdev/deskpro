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
require_once DP_ROOT.'/sys/load_config.php';
require DP_ROOT.'/sys/system.php';

$dirs = array(
    DP_ROOT.'/src',
    DP_ROOT.'/vendor-src/metadata/src',
);

$no_ns = array(
    DP_ROOT.'/vendor-src/swiftmailer/lib/classes',
);

$map = array("<?php return array(");

foreach ($dirs as $d) {
    $finder = new \Symfony\Component\Finder\Finder();
    $finder->files()->name('*.php')->in($d);

    $is_no_ns = in_array($d, $no_ns);

    foreach ($finder as $file) {
        /** @var \SplFileInfo $file */

        $path = $file->getRealPath();
        $path = str_replace('\\', '/', $path);

        $dir_suffix = str_replace(DP_ROOT, '', $d);

        $class      = str_replace($d.'/', '', $path);
        $class_file = $class;

        $class = str_replace('/', '\\', $class);
        $class = str_replace('.php', '', $class);

        if ($is_no_ns) {
            $class = str_replace('\\', '_', $class);
        }

        $line = sprintf("\t%-100s => %s,", "'$class'", "DP_ROOT.'$dir_suffix/$class_file'");
        echo ".";

        $map[] = $line;
    }
}

echo "\n";

$map[] = ");";
$map   = implode("\n", $map);

file_put_contents(dp_get_cache_dir().'/classmap.php', $map);
unset($map);
