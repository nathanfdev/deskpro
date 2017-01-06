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

namespace DpSys\Boot\BootTask;

use Symfony\Component\HttpFoundation\Request;

/**
 * This creates the proper Request object.
 */
class RequestBootTask implements BootTaskInterface
{
    public function run(\DpRun\DpEnv $env, array $resources)
    {
        $request = Request::createFromGlobals();

        if (!defined('DP_REQUEST_URL')) {
            define('DP_REQUEST_URL', $request->getUri());
        }

        // get request id from cgi params (e.g. nginx "fastcgi_param DP_REQUEST_ID foo")
        if (!empty($_SERVER['DP_REQUEST_ID'])) {
            $requestId = $_SERVER['DP_REQUEST_ID'];

        // we might have a proxy that provides us with a request ID via a header
        } elseif (!empty($_SERVER['HTTP_X_REQUEST_ID']) && $env->getConfig('env.use_request_id_header')) {
            $requestId = $_SERVER['HTTP_X_REQUEST_ID'];

        // otherwise lets generate it ourselves
        } else {
            if (function_exists('openssl_random_pseudo_bytes')) {
                $requestId = ceil(time() / 60).'-'.bin2hex(openssl_random_pseudo_bytes(30));
            } else {
                $requestId = ceil(time() / 60).'-'.substr(sha1(mt_rand(1000, 9999).mt_rand(1000, 9999).mt_rand(1000, 9999)).sha1(uniqid('', true)), 0, 60);
            }
        }

        $request->attributes->set('request_id', $requestId);
        $env->setRuntimeVar('request', $request);

        return [
            'request' => $request,
        ];
    }
}
