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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\EventListener\ClientMessage;

use Application\DeskPRO\Entity\ClientMessage;
use DeskPRO\Bundle\AppBundle\DataSerializer\DataSerializer;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ClientMessageListener.
 */
class ClientMessageListener implements EventSubscriberInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var DataSerializer
     */
    private $data_serializer;

    /**
     * Constructor.
     *
     * @param EntityManager  $em
     * @param DataSerializer $data_serializer
     */
    public function __construct(EntityManager $em, DataSerializer $data_serializer)
    {
        $this->em              = $em;
        $this->data_serializer = $data_serializer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ClientMessageEvent::SEND_MESSAGE => 'onSendMessage',
        ];
    }

    /**
     * @param ClientMessageEvent $event
     */
    public function onSendMessage(ClientMessageEvent $event)
    {
        $data = $event->getData();
        if (is_object($data)) {
            $data = $this->data_serializer->serialize($data)['data'];
        }

        $client_message = new ClientMessage();
        $client_message
            ->setChannel($event->getChannel())
            ->setData($data)
            ->setCreatedByClient($event->getCreatedBy())
        ;

        $this->em->persist($client_message);
        $this->em->flush();
    }
}
