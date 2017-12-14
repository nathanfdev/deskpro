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

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Voice;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageVoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Voice\VoicePhoneCall as VoicePhoneCallModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use Doctrine\ORM\EntityManager;

/**
 * Class VoicePhoneCallHandler.
 */
class VoicePhoneCallHandler extends AbstractEntityHandler
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var array
     */
    private $callIds = [];

    /**
     * @var Ticket[]
     */
    private $tickets;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return VoicePhoneCall::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param VoicePhoneCall $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $this->callIds[$entity->getId()] = true;

        $model = new VoicePhoneCallModel($entity);
        $model->setTicket(new CallbackDeferredProperty([$this, 'getTicket'], [$entity]));

        return $model;
    }

    /**
     * @internal
     *
     * @param VoicePhoneCall $entity
     *
     * @return Ticket
     */
    public function getTicket(VoicePhoneCall $entity)
    {
        if (null === $this->tickets) {
            $qb = $this->em->createQueryBuilder();
            $qb
                ->select('a', 'm', 't')
                ->from(TicketMessageVoicePhoneCall::class, 'a')
                ->join('a.message', 'm')
                ->join('m.ticket', 't')
                ->where('a.phoneCall IN(:ids)')
                ->setParameter('ids', array_keys($this->callIds))
            ;

            /** @var TicketMessageVoicePhoneCall[] $result */
            $result = $qb->getQuery()->getResult();
            foreach ($result as $attribute) {
                $this->tickets[$attribute->getPhoneCall()->getId()] = $attribute->getMessage()->getTicket();
            }
        }

        return isset($this->tickets[$entity->getId()]) ? $this->tickets[$entity->getId()] : null;
    }
}
