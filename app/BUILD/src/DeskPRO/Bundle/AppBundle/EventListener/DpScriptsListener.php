<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

namespace DeskPRO\Bundle\AppBundle\EventListener;

use DpSys\CodePlugin\CodePluginManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class DpScriptsListener.
 */
class DpScriptsListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            // call before RouterListener
            KernelEvents::REQUEST => ['onRequest', 40],
        ];
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!preg_match('#/scripts/(agent|user)/(\w+)/(\w+)#', $request->getPathInfo(), $m)) {
            return;
        }

        list(, $context, $controller, $action) = $m;

        $controller = CodePluginManager::getManager()->routeScriptController($request, $context, $controller, $action);
        if (!$controller) {
            $controller = 'DpScripts\\'.ucfirst($context).'\\'.ucfirst($controller).'Controller::'.$action.'Action';
        }

        $request->attributes->add([
            '_route'        => 'scripts_'.$controller.'_'.$action,
            '_controller'   => $controller,
            '_route_params' => [],
        ]);
    }
}
