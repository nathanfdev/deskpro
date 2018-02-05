<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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
