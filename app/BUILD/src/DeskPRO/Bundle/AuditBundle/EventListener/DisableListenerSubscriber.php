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
