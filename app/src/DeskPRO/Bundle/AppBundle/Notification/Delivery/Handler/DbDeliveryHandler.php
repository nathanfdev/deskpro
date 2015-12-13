<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler;

use Application\DeskPRO\ORM\EntityManager;
use DeskPRO\Bundle\AppBundle\Entity\ActionAlert as ActionAlertEntity;
use DeskPRO\Bundle\AppBundle\Entity\Notification as NotificationEntity;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\Notification;

/**
 * Class DbDeliveryHandler.
 */
class DbDeliveryHandler extends AbstractDeliveryHandler
{
    const TYPE = 'notification.delivery.handler.db';

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function deliver(MessageInterface $message)
    {
        $persistTo = $this->getChannel($message);
        $persistTo
            ->setUuid($message->getId())
            ->setTargetId($message->getTarget())
            ->setDateCreated(new \DateTime($message->getDate()))
            ->setData($message->getData())
            ->setIsDismissed(false);
        $this->em->persist($persistTo);
        $this->em->flush();
    }

    /**
     * @param MessageInterface $message
     *
     * @return ActionAlertEntity|NotificationEntity
     */
    protected function getChannel(MessageInterface $message)
    {
        if ($message instanceof ActionAlert) {
            return new ActionAlertEntity();
        } elseif ($message instanceof Notification) {
            return new NotificationEntity();
        }

        throw new \InvalidArgumentException('Message should be ActionAlert or Notification');
    }
}
