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
