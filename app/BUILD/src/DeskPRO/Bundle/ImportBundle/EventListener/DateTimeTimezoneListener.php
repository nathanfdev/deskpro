<?php

namespace DeskPRO\Bundle\ImportBundle\EventListener;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class DateTimeTimezoneListener.
 */
class DateTimeTimezoneListener implements EventSubscriberInterface
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
            ConsoleEvents::COMMAND => ['onCommand', 2],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @param ConsoleCommandEvent $event
     */
    public function onCommand(ConsoleCommandEvent $event)
    {
        $command = $event->getCommand();
        if (strpos($command->getName(), 'dp:import') !== 0) {
            return;
        }

        // set specific timezone for datetime fields at runtime
        $timezone = $this->container->get('settings_resolver')->getGlobalSettings()->get('importer_timezone', 'UTC');
        if ($timezone) {
            $datetimeHandler = $this->container->get('jms_serializer.datetime_handler');

            $reflection = new \ReflectionClass($datetimeHandler);
            $property   = $reflection->getProperty('defaultTimezone');
            $property->setAccessible(true);
            $property->setValue($datetimeHandler, new \DateTimeZone($timezone));
            $property->setAccessible(false);
        }
    }
}
