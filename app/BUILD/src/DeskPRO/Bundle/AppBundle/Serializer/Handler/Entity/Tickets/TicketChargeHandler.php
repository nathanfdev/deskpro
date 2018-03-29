<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Tickets;

use Application\DeskPRO\Entity\CustomDataBilling;
use Application\DeskPRO\Entity\CustomDefBilling;
use Application\DeskPRO\Entity\TicketCharge as TicketChargeEntity;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketCharge as TicketChargeModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use Doctrine\ORM\EntityManager;

/**
 * Class TicketChargeHandler.
 */
class TicketChargeHandler extends AbstractEntityHandler
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var int[]
     */
    private $chargeIds = [];

    /**
     * @var string[]
     */
    private $comments;

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
        return TicketChargeEntity::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param TicketChargeEntity $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $this->chargeIds[] = $entity->getId();

        $model = new TicketChargeModel($entity);
        $model->setComment(new CallbackDeferredProperty([$this, 'getComment'], [$entity]));

        return $model;
    }

    /**
     * @param TicketChargeEntity $entity
     *
     * @return string
     */
    public function getComment(TicketChargeEntity $entity)
    {
        if (null === $this->comments) {
            $customDef = $this->em->getRepository(CustomDefBilling::class)->findOneBy(['title' => 'Comment']);
            if ($customDef) {
                $result = $this->em->getRepository(CustomDataBilling::class)->findBy([
                    'ticket_charge' => $this->chargeIds,
                    'root_field'    => $customDef,
                ]);

                foreach ($result as $customData) {
                    $this->comments[$customData->getTicketCharge()->getId()] = $customData->getInput();
                }
            } else {
                $this->comments = [];
            }
        }

        if (isset($this->comments[$entity->getId()])) {
            return $this->comments[$entity->getId()];
        }

        return '';
    }
}
