<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

return [
    'geoip_api' => [
        'into'    => DP_ROOT.'/vendor-src/geoip-api',
        'repos'   => 'git://github.com/maxmind/geoip-api-php.git',
        'version' => 'HEAD',
    ],
    'idbstore' => [
        'into'    => DP_WEB_ROOT.'/web/vendor-src/idbstore',
        'repos'   => 'git://github.com/jensarps/IDBWrapper.git',
        'version' => 'v1.1.0',
    ],
    'metadata' => [
        'into'    => DP_ROOT.'/vendor-src/metadata',
        'repos'   => 'git://github.com/schmittjoh/metadata.git',
        'version' => '1.1.0',
    ],
    'php5_akismet' => [
        'into'    => DP_ROOT.'/vendor-src/php5-akismet',
        'repos'   => 'https://github.com/achingbrain/php5-akismet.git',
        'version' => 'HEAD',
    ],
    'querypath' => [
        'into'    => DP_ROOT.'/vendor-src/querypath',
        'repos'   => 'git://github.com/technosophos/querypath.git',
        'version' => 'HEAD',
    ],
    'twig_js' => [
        'into'    => DP_WEB_ROOT.'/web/vendor-src/twig',
        'repos'   => 'git://github.com/justjohn/twig.js.git',
        'version' => 'HEAD',
    ],
];
