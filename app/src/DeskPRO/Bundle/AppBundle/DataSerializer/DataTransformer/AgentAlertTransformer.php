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

namespace DeskPRO\Bundle\AppBundle\DataSerializer\DataTransformer;

use DeskPRO\Bundle\AppBundle\DataSerializer\DataTransformerRequest;
use Doctrine\ORM\EntityManager;

class AgentAlertTransformer extends AbstractDataSerializerTransformer
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param DataTransformerRequest $transformation_request
     *
     * @return mixed
     */
    public function getAutomaticProperties(DataTransformerRequest $transformation_request)
    {
        return [
            'date_created',
            'is_dismissed',
        ];
    }

    /**
     * @param DataTransformerRequest $transformation_request
     *
     * @return mixed
     */
    public function getCustomProperties(DataTransformerRequest $transformation_request)
    {
        /* @var \Application\DeskPRO\Entity\AgentAlert $data */
        $alert   = $transformation_request->getDataToBeTransformed();
        $data    = $alert->getData();
        $newData = [
            'notificationTitle'   => '',
            'notificationSummary' => '',
        ];
        $typeName = $alert->getTypename();
        switch ($typeName) {
            case 'tickets':
                $ticket = $this->em->getRepository('DeskPRO:Ticket')->find($data['ticket']);
                if (null !== $ticket) {
                    $newData['notificationSummary'] = $ticket->getSubject();
                    if ($data['is_new_ticket']) {
                        $typeName .= '.new_ticket';
                        $newData['notificationTitle'] = 'New ticket by ';
                    } elseif ($data['is_new_agent_reply']) {
                        $typeName .= '.new_message.agent_reply';
                        $newData['notificationTitle'] = 'Agent reply by ';
                    } elseif ($data['is_new_agent_note']) {
                        $typeName .= '.new_message.agent_note';
                        $newData['notificationTitle'] = 'Agent note by ';
                    } elseif ($data['is_new_user_reply']) {
                        $typeName .= '.new_message.user_reply';
                        $newData['notificationTitle'] = 'Reply by ';
                    } else {
                        $typeName .= '.updated';
                        $newData['notificationTitle'] = 'Updated by ';
                    }
                    $person  = $this->em->getRepository('DeskPRO:Person')->find($data['performer']);
                    $contact = null === $person ? '' : $person->getName().' ('.$person->getPrimaryEmail()->getEmail().')';
                    $newData['notificationTitle'] .= $contact;
                }
                break;
        }

        return [
            'uuid'     => $alert->getId(),
            'data'     => $newData,
            'target'   => $alert->getPerson()->getId(),
            'typename' => $typeName,
        ];
    }
}
