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
