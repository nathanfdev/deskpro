<?php

namespace Application\DeskPRO\Log\Handler;

use Application\DeskPRO\Entity\LogEvent;

class LogEventHandler extends DBHandler
{
    /**
     * {@inheritdoc}
     */
    public function isHandling(array $record)
    {
        return true;
    }

    /**
     * @param array $record
     */
    protected function write(array $record)
    {
        if (!(isset($record['context']['_entity']) && $record['context']['_entity'] instanceof LogEvent)) {
            return;
        }

        /** @var LogEvent $entity */
        $entity = $record['context']['_entity'];
        $entity->prepare();

        parent::write($record);
    }
}
