<?php

/**
 * DeskPRO.
 *
 * @category Controller
 */

namespace Application\LegacyApiBundle\StaticLoader;

use Application\DeskPRO\App;

class RequestKey
{
    public static function getApiKeyFromRequest()
    {
        $em      = App::getOrm();
        $request = App::getRequest();

        static $api_key = null;

        if ($api_key !== null) {
            return $api_key;
        }

        $headers = $request->server->getHeaders();
        $key_str = false;
        if ($request->headers->get('X-DeskPRO-API-Key', null, true)) {
            $key_str = $request->headers->get('X-DeskPRO-API-Key', null, true);
        } elseif (!empty($_REQUEST['API-KEY'])) {
            $key_str = $_REQUEST['API-KEY'];
        } elseif (!empty($headers['PHP_AUTH_USER']) and !empty($headers['PHP_AUTH_PW'])) {
            $key_str = $headers['PHP_AUTH_USER'].':'.$headers['PHP_AUTH_PW'];
        }

        if (!$key_str) {
            $api_key = false;

            return false;
        }

        $api_key = $em->getRepository('DeskPRO:ApiKey')->findByKeyString($key_str);
        if ($api_key) {
            return $api_key;
        }

        $api_key = false;

        return false;
    }

    public static function getApiTokenFromRequest()
    {
        $em      = App::getOrm();
        $request = App::getRequest();

        static $api_token = null;

        if ($api_token !== null) {
            return $api_token;
        }

        $headers   = $request->server->getHeaders();
        $token_str = false;
        if ($request->headers->get('X-DeskPRO-API-Token', null, true)) {
            $token_str = $request->headers->get('X-DeskPRO-API-Token', null, true);
        } elseif (!empty($_REQUEST['API-TOKEN'])) {
            $token_str = $_REQUEST['API-TOKEN'];
        } elseif (!empty($headers['PHP_AUTH_USER']) and !empty($headers['PHP_AUTH_PW'])) {
            $token_str = $headers['PHP_AUTH_USER'].':'.$headers['PHP_AUTH_PW'];
        }

        if (!$token_str) {
            $api_token = false;

            return false;
        }

        $api_token = $em->getRepository('DeskPRO:ApiToken')->findByTokenString($token_str);
        if ($api_token) {
            return $api_token;
        }

        $api_token = false;

        return false;
    }
}
