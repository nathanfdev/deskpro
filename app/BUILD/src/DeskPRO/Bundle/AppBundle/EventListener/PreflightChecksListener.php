<?php

namespace DeskPRO\Bundle\AppBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Runs basic checks to determine if we should go to the install screen.
 */
class PreflightChecksListener implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onPreRequest', 3000],
        ];
    }

    /**
     * @param GetResponseEvent $event
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function onPreRequest(GetResponseEvent $event)
    {
        // Missing PDO
        if (!extension_loaded('pdo')) {
            $r = new Response("DeskPRO is not installed.\nError: missing_pdo_extension", 423, ['Content-Type' => 'text/plain']);
            $event->setResponse($r);
            $event->stopPropagation();

            return;
        }

        // Bad DB connection
        try {
            $settings = $this->container->get('settings_resolver')->getGlobalSettings(true);
        } catch (\Doctrine\DBAL\DBALException $e) {
            /* @var \DpRun\DpEnv $DP_ENV */
            global $DP_ENV;

            if (!$DP_ENV->getConfig('database.host') && !$DP_ENV->getConfig('database.0.host')) {
                $r = new Response("DeskPRO is not installed.\nError: missing_config", 423, ['Content-Type' => 'text/plain']);
                $event->setResponse($r);
                $event->stopPropagation();

                return;
            } else {
                throw $e;
            }
        }

        if (!$this->container->get('doctrine')->getConnection()->isConnected()) {
            /* @var \DpRun\DpEnv $DP_ENV */
            global $DP_ENV;

            if (!$DP_ENV->getConfig('database.host') && !$DP_ENV->getConfig('database.0.host')) {
                $r = new Response("DeskPRO is not installed.\nError: missing_config", 423, ['Content-Type' => 'text/plain']);
                $event->setResponse($r);
                $event->stopPropagation();
            } else {
                $r = new Response("Can't connect to database.\nError: connection_error", 423, ['Content-Type' => 'text/plain']);
                $event->setResponse($r);
                $event->stopPropagation();
            }

            return;
        }

        // Not installed yet
        if (!$settings->get('core.install_build')) {
            $r = new Response("DeskPRO is not installed.\nError: install_incomplete", 423, ['Content-Type' => 'text/plain']);
            $event->setResponse($r);
            $event->stopPropagation();

            return;
        }
    }
}
