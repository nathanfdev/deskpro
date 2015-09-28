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

require DP_ROOT.'/sys/load_config.php';
dp_load_config();

if (0 && !@$GLOBALS['DP_USING_TESTING_CONFIG']) {
    header('Content-Type: text/plain');
    echo 'Not in testing mode.';
    exit(1);
}

if (empty($_GET['f'])) {
    header('Content-Type: text/plain');
    echo 'No file specified';
    exit(1);
}

function get_test_file_path($path)
{
    $path = realpath(DP_WEB_ROOT.'/web/'.trim($path, '/'));
    if (!$path || !is_file($path) || strpos($path, DP_WEB_ROOT) !== 0) {
        return;
    }

    return $path;
}

$path = get_test_file_path($_GET['f']);

if (!$path) {
    header('Content-Type: text/plain');
    echo 'File does not exist';
    exit(1);
}

$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
switch ($ext) {
    case 'html': header('Content-Type: text/html'); break;
    case 'js':   header('Content-Type: text/javascript'); break;
    case 'json': header('Content-Type: application/json'); break;
    case 'xml':  header('Content-Type: text/xml'); break;
    default: header('Content-Type: text/plain'); break;
}

$content = file_get_contents($path);

function process_content_replacements($content)
{
    return preg_replace_callback('/<!\-\-#include\s*file="(.*?)"\s*\-\->/', function ($m) {
        $path = get_test_file_path(trim($m[1]));
        if ($path) {
            return process_content_replacements(file_get_contents($path));
        } else {
            return '<!-- include error -- unknown file: '.htmlspecialchars($m[1]).' -->';
        }
    }, $content);
}

if ($ext == 'html') {
    $content = process_content_replacements($content);
}

echo $content;
exit;
