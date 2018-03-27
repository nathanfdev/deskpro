<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AuditBundle\EventListener;

use DeskPRO\Bundle\InstallBundle\Command\InstallCommand;
use DeskPRO\Bundle\UpdateBundle\Command\UpdateCommand;
use Doctrine\Bundle\FixturesBundle\Command\LoadDataFixturesDoctrineCommand;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Disables the audit log listener when we don't want it.
 */
class DisableListenerSubscriber implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::COMMAND => ['onCommand', 0],
        ];
    }

    public function onCommand(ConsoleCommandEvent $event)
    {
        $cmd = $event->getCommand();

        if ($cmd instanceof InstallCommand || $cmd instanceof LoadDataFixturesDoctrineCommand || $cmd instanceof UpdateCommand) {
            $this->disableListener();
        }
    }

    private function disableListener()
    {
        if ($this->container->has('audit_log.doctrine_listener')) {
            $this->container->get('audit_log.doctrine_listener')->disableListener();
        }
    }
}
