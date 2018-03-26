<?php

namespace DeskPRO\Bundle\UpdateBundle\Command\ConsoleEvents;

use DeskPRO\Bundle\UpdateBundle\Command\AutoUpdateCommand;
use DeskPRO\Bundle\UpdateBundle\Command\AutoUpgradeStatusCommand;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UpgradeSessionListener implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * UpgradeSessionListener constructor.
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
            ConsoleEvents::COMMAND => ['onCommand', 0],
        ];
    }

    public function onCommand(ConsoleCommandEvent $event)
    {
        // These two command manage the sid state manually
        if (
            $event->getCommand() instanceof AutoUpgradeStatusCommand
            || $event->getCommand() instanceof AutoUpdateCommand
        ) {
            return;
        }

        $input = $event->getInput();
        if ($input->hasOption('session_id')) {
            $sessionId      = $input->hasOption('session_id');
            $managerFactory = $this->container->get('dp.updater.session_manager_factory');
            $managerFactory->enableSessionId($sessionId);
        }
    }
}
