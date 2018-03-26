<?php

namespace Application\DeskPRO\Entity\EventListener;

use Doctrine\Common\EventSubscriber;

class SearchUpdater implements EventSubscriber
{
    public function __construct()
    {
    }
    public function getSubscribedEvents()
    {
        return [];
    }
}
