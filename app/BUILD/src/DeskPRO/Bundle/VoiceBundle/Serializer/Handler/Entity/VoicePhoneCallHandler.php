<?php

namespace DeskPRO\Bundle\VoiceBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Entity\TicketMessageVoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\VoiceBundle\Serializer\Model\VoicePhoneCall as VoicePhoneCallModel;
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
