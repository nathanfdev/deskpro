<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\EventListener;

use DeskPRO\Bundle\AppBundle\Annotation\AutoPostOnGetRequest;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class AutoPostOnGetRequestListener.
 */
class AutoPostOnGetRequestListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => ['onController', -1],
       ];
    }

    public function onController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();

        // ensure the correct annotation is on the controller
        if (!$request->attributes->has(AutoPostOnGetRequest::ALIAS_WITH_UNDERSCORE)) {
            return;
        }

        // if it's a safe request method, we want to force a POST
        if ($request->isMethodSafe()) {

            // resolve the request via a simple closure controller
            $controller = function (Request $request) {
                $html = <<< 'HTML'
<html>
<head>
<title>Redirecting</title>
</head>
<body>
<form method="POST" id="auto-submit">
<input type="submit" value="Continue" />
</form>
<script>
var tid = setInterval( function () {
    if ( document.readyState !== 'complete' ) return;
    clearInterval( tid );
    document.getElementById('auto-submit').submit();
}, 50 );
</script>
</body>
</html>
HTML;

                return new Response($html);
            };

            $event->setController($controller);
        }
    }
}
