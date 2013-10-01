<?php return array(
	'geoip_api' => array(
		'into'    => DP_ROOT.'/vendor-src/geoip-api',
		'repos'   => 'git://github.com/maxmind/geoip-api-php.git',
		'version' => 'HEAD',
	),
	'idbstore' => array(
		'into'    => DP_WEB_ROOT.'/web/vendor-src/idbstore',
		'repos'   => 'git://github.com/jensarps/IDBWrapper.git',
		'version' => 'v1.1.0',
	),
	'metadata' => array(
		'into'    => DP_ROOT.'/vendor-src/metadata',
		'repos'   => 'git://github.com/schmittjoh/metadata.git',
		'version' => '1.1.0',
	),
	'php5_akismet' => array(
		'into'    => DP_ROOT.'/vendor-src/php5-akismet',
		'repos'   => 'https://github.com/achingbrain/php5-akismet.git',
		'version' => 'HEAD',
	),
	'querypath' => array(
		'into'    => DP_ROOT.'/vendor-src/querypath',
		'repos'   => 'git://github.com/technosophos/querypath.git',
		'version' => 'HEAD',
	),
	'twig_js' => array(
		'into'    => DP_WEB_ROOT.'/web/vendor-src/twig',
		'repos'   => 'git://github.com/justjohn/twig.js.git',
		'version' => 'HEAD',
	)
);
