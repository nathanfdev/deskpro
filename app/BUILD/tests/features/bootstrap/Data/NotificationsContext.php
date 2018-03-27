<?php

namespace DpBehat\Data;

use DeskPRO\Bundle\AppBundle\Entity\ActionAlert;
use DeskPRO\Bundle\AppBundle\Entity\Notification;
use DpBehat\BaseContext;

/**
 * Class NotificationsContext.
 */
class NotificationsContext extends BaseContext
{
    /**
     * @Given there should be :number action_alert(s) for me
     *
     * @param int $number
     */
    public function thereShouldBeActionAlertsForMe($number)
    {
        $me           = DataContext::getReference('me');
        $actionAlerts = $this->em()->getRepository(ActionAlert::class)->findBy(['target_id' => $me->getId()]);
        $message      = sprintf('Should be %d action alerts, but %d found', $number, count($actionAlerts));
        $this->assert(count($actionAlerts) === (int) $number, $message);
    }

    /**
     * @Given there should be :number action_alert(s)
     *
     * @param int $number
     */
    public function thereShouldBeActionAlerts($number)
    {
        $actionAlerts = $this->em()->getRepository(ActionAlert::class)->findAll();
        $message      = sprintf('Should be %d action alerts, but %d found', $number, count($actionAlerts));
        $this->assert(count($actionAlerts) === (int) $number, $message);
    }

    /**
     * @Given there should be :number notification(s) for me
     *
     * @param int $number
     */
    public function thereShouldBeNotificationsForMe($number)
    {
        $me            = DataContext::getReference('me');
        $notifications = $this->em()->getRepository(Notification::class)->findBy(['target_id' => $me->getId()]);
        $message       = sprintf('Should be %d notifications, but %d found', $number, count($notifications));
        $this->assert(count($notifications) === (int) $number, $message);
    }

    /**
     * @Given there should be :number notification(s)
     *
     * @param int $number
     */
    public function thereShouldBeNotifications($number)
    {
        $notifications = $this->em()->getRepository(Notification::class)->findAll();
        $message       = sprintf('Should be %d notifications, but %d found', $number, count($notifications));
        $this->assert(count($notifications) === (int) $number, $message);
    }
}
