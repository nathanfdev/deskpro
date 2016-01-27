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

$DP_CONFIG['debug']['dev']            = true;
$DP_CONFIG['trust_proxy_data']        = array();
$DP_CONFIG['serve_file_debug']        = true;
$DP_CONFIG['disable_url_corrections'] = false;

// Use webpack server
$DP_CONFIG['pub_asset_urls'] = array('pub/build' => 'http://localhost:9666/pub/build/');

// Disabel ticket dupe checkings
$DP_CONFIG['debug']['disable_dupe_check'] = true;

// Use raw assets in old agent
$DP_CONFIG['debug']['raw_assets'] = array('all');

// Use the /web/stylesheets as static compiled versions of the less
// files used in agnet. Otherwise, client-side LESS (JS) will be used
//$DP_CONFIG['debug']['less_use_css_dir'] = true;

// Dont report errors
$DP_CONFIG['debug']['no_report_errors'] = true;
