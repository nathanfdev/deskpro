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

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\AgentAlert as AgentAlertEntity;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Serializer\Model\AgentAlerts\AgentAlert as AgentAlertModel;
use DeskPRO\Bundle\AppBundle\Serializer\Model\AgentAlerts\AgentAlertData;
use DeskPRO\Bundle\AppBundle\Serializer\Model\AgentAlerts\NotifyData;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * Class AgentAlertHandler.
 */
class AgentAlertHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return AgentAlertEntity::class;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(JsonSerializationVisitor $visitor, $entity, $type, SideloadSerializationContext $context)
    {
        /* @var AgentAlertEntity $entity */
        $data      = $entity->getData();
        $sideloads = $context->getSideloadStore();
        if ($data['ticket']) {
            $sideloads->addSideloadString(Ticket::class, $data['ticket']);
        }
        if ($data['performer']) {
            $sideloads->addSideloadString(Person::class, $data['performer']);
        }

        return parent::serialize($visitor, $entity, $type, $context);
    }

    /**
     * {@inheritdoc}
     *
     * @param AgentAlertEntity $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $type = 'notifications.'.$entity->getTypename();
        $data = $entity->getData();

        if ($entity->getTypename() === 'tickets') {
            if ($data['is_new_ticket']) {
                $type .= '.new_ticket';
            } elseif ($data['is_new_agent_reply']) {
                $type .= '.new_message.agent_reply';
            } elseif ($data['is_new_agent_note']) {
                $type .= '.new_message.agent_note';
            } elseif ($data['is_new_user_reply']) {
                $type .= '.new_message.user_reply';
            } else {
                $type .= '.updated';
            }
        }

        $alertData = new AgentAlertData($data['ticket'], new NotifyData($data['browser_rendered']), $data['performer']);
        $model     = new AgentAlertModel($entity, $type, $alertData);

        return $model;
    }
}
