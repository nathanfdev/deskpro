<?php

/**
 *   audit_log.decide_listener:.
 */

namespace DeskPRO\Bundle\MessengerBundle\Security\EventListener;

use DeskPRO\Component\Util\RegexUtils;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class VisitorIdListener
{
    const HTTP_REALM          = 'realm="x-deskpro-visitorid DeskPRO User Messenger API"';
    const VISITOR_HEADER_NAME = 'X-Deskpro-VisitorID';

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request  = $event->getRequest();
        $pathInfo = $request->getPathInfo();
        if (
            $event->isMasterRequest()
            && !RegexUtils::safePregMatch('#^/api/messenger/?$#', $pathInfo)
            && !RegexUtils::safePregMatch('#^/api/messenger/service/setup/?$#', $pathInfo)
            && !RegexUtils::safePregMatch('#^/api/messenger/service/blob/?$#', $pathInfo)
            && RegexUtils::safePregMatch('#^/api/messenger#', $pathInfo)
        ) {
            if (!$request->headers->has(self::VISITOR_HEADER_NAME)) {
                throw new UnauthorizedHttpException(self::HTTP_REALM, self::VISITOR_HEADER_NAME.' header is not set. Can\'t auth');
            }
        }
    }
}
