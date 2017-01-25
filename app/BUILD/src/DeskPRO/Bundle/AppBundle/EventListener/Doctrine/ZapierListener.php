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

namespace DeskPRO\Bundle\AppBundle\EventListener\Doctrine;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use DeskPRO\Bundle\AppBundle\Entity\ZapierHook;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\AppBundle\Util\HttpClient;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Serializer;

class ZapierListener
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var Serializer
     */
    protected $serializer;

    public function __construct(EntityManager $entityManager, Serializer $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer    = $serializer;
    }

    /**
     * @ORM\PrePersist()
     *
     * @param ZapierHook $zapierHook
     */
    public function onCreate(ZapierHook $zapierHook)
    {
        $this->sendSampleData($zapierHook);
    }

    /**
     * @param ZapierHook $zapierHook
     *
     * @throws \Exception
     */
    public function sendSampleData(ZapierHook $zapierHook)
    {
        $httpClient = new HttpClient(['timeout' => 30]);

        $serializationContext = new SideloadSerializationContext();

        $options = [];
        switch ($zapierHook->getEvent()) {
            case 'ticket_created':
                $ticket          = $this->entityManager->getRepository(Ticket::class)->findOneBy([]);
                $options['body'] = $this->serializer->serialize($ticket, 'json', $serializationContext);
                break;
            case 'new_ticket_reply':
                $ticket          = $this->entityManager->getRepository(TicketMessage::class)->findOneBy([]);
                $options['body'] = $this->serializer->serialize($ticket, 'json', $serializationContext);
                break;
            default:
                throw new \Exception('Unknown event '.$zapierHook->getEvent());
        }
        $httpClient->request('POST', $zapierHook->getTargetUrl(), $options);
    }
}
