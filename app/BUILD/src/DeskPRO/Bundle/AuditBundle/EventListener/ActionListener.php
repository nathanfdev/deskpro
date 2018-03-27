<?php

namespace DeskPRO\Bundle\AuditBundle\EventListener;

use DeskPRO\Bundle\AuditBundle\Event\LogEvent;

class ActionListener
{
    public function setAction(LogEvent $event)
    {
        $metadata = $event->getMetadata();
        $log      = $event->getLog();
        $log->setAction($metadata->table['name'].'.'.$event->getContext()->getAction());
    }
}
