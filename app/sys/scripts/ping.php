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

$type = !empty($_REQUEST['type']) ? (string) $_REQUEST['type'] : 'text';

switch ($type) {
    case 'json':
        header('Content-Type: application/json');
        echo json_encode(array('response' => 'pong'));
        break;
    case 'jsonp':
        $callback = !empty($_REQUEST['jsonp']) ? (string) $_REQUEST['jsonp'] : null;
        if (!$callback) {
            $callback = !empty($_REQUEST['callback']) ? (string) $_REQUEST['callback'] : null;
        }

        $callback = preg_replace('#[^a-zA-Z0-9_\.]#', '', $callback);
        if (!$callback) {
            $callback = 'callback';
        }

        header('Content-Type: text/javascript');
        echo $callback.'('.json_encode(array('response' => 'pong')).');';
        break;
    default:
        header('Content-Type: text/plain');
        echo 'pong';
        break;
}
