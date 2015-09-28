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

define('DP_ROOT', realpath(dirname(__FILE__).'/../'));

require_once DP_ROOT.'/vendor/symfony/symfony/src/Symfony/Component/Finder/Finder.php';
require_once DP_ROOT.'/src/Application/DeskPRO/Distribution/VerifyChecksums.php';

$t_start = microtime(true);
$verify  = new \Application\DeskPRO\Distribution\VerifyChecksums(150);

echo "Will now check {$verify->countFiles()} files.\n\n";

for ($i = 0; $i < $verify->countChunks(); ++$i) {
    $results = $verify->compareChunk($i);

    if ($results['added']) {
        foreach ($results['added'] as $f) {
            echo "\n[UNKNOWN] $f";
        }
    }
    if ($results['removed']) {
        foreach ($results['removed'] as $f) {
            echo "\n[MISSING] $f";
        }
    }
    if ($results['changed']) {
        foreach ($results['changed'] as $f) {
            echo "\n[INVALID] $f";
        }
    }

    if (!$results['added'] && !$results['removed'] && !$results['changed']) {
        echo '.';
    } else {
        echo "\n";
    }
}

echo "\n\n";
printf('Done in %.3fs', microtime(true) - $t_start);
