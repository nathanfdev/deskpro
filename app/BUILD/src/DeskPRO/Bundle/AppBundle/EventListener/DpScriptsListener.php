<?php

namespace DeskPRO\Bundle\AppBundle\EventListener;

use DeskPRO\Component\Util\StringUtils;
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
        if (!preg_match('#/scripts/(?P<context>agent|user)/(?P<controller>[a-zA-Z0-9\-_:]+)(?:/(?P<action>[a-zA-Z0-9\-_:]+))?#', $request->getPathInfo(), $m)) {
            return;
        }

        $context    = $m['context'];
        $controller = $m['controller'];
        $action     = @$m['action'] ?: 'index';

        $controllerClass = \DpSys\CodePlugin\DpPlugins::getManager()->routeScriptController($request, $context, $controller, $action);
        if (!$controllerClass) {
            $controllerClass = 'DpScripts\\'.ucfirst($context).'\\'.StringUtils::toCamelCase(str_replace('-', '_', $controller)).'Controller::'.StringUtils::toCamelCase(str_replace('-', '_', $action)).'Action';
        }

        $request->attributes->add([
            '_route'        => 'scripts_'.$controller.'_'.$action,
            '_controller'   => $controllerClass,
            '_route_params' => [],
        ]);
    }
}
