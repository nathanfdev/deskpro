<?php

namespace DeskPRO\Bundle\AuditBundle\EventListener;

use DeskPRO\Bundle\AuditBundle\Event\LogEvent;

class DescriptionListener
{
    private $verbMap = [
        AuditListener::UPDATE => 'updated',
        AuditListener::INSERT => 'created',
        AuditListener::REMOVE => 'deleted',
    ];

    public function setDescription(LogEvent $event)
    {
        $action = $event->getContext()->getAction();

        $description = sprintf(
            '[%s:%s] %s.',
            $event->getLog()->getObjectType(),
            $event->getLog()->getObjectId(),
            $this->getVerb($action)
        );

        if ($action === AuditListener::UPDATE) {
            $description .= sprintf(
                ' Updated fields: %s.',
                implode(', ', array_keys($event->getLog()->getData()->getDiff()))
            );
        }

        $event->getLog()->setDescription($description);
    }

    private function getVerb($action)
    {
        if (isset($this->verbMap[$action])) {
            return $this->verbMap[$action];
        } else {
            return sprintf('subjected to the effects of an [ %s ] action', $action);
        }
    }
}
