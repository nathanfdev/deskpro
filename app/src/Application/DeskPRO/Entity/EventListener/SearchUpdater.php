<?php
// DUMMY FILE - fix for upgrade perm error when trying to delete files
// Will be removed in future version

namespace Application\DeskPRO\Entity\EventListener;
use Doctrine\Common\EventSubscriber;
class SearchUpdater implements EventSubscriber
{
    public function __construct() {}
    public function getSubscribedEvents() { return array(); }
}
