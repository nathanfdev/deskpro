<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Server\ServerPhpInfo;

class ServerPhpInfoService
{
    public static function create(DeskproContainer $container)
    {
        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        $baseUrl = '/';

        if ($container->has('request_stack')) {
            $req = $container->get('request_stack')->getMasterRequest();
            if ($req) {
                $baseUrl = rtrim($req->getUriForPath('/'), '/');
            }
        }

        return new ServerPhpInfo(
            $container->get('deskpro.app_env'),
            $baseUrl,
            $DP_ENV->getDatManager()->readTxtFile('server_info_auth', '')
        );
    }
}
