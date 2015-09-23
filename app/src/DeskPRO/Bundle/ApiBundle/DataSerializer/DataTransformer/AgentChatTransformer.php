<?php
/**
 * deskpro.
 * @author Denis Ranneft (aka Immortal) <denis@ranneft.ru>
 * Date: 11.09.15
 * Time: 20:58
 */

namespace DeskPRO\Bundle\ApiBundle\DataSerializer\DataTransformer;

use DeskPRO\Bundle\ApiBundle\DataSerializer\DataTransformer\AbstractDataSerializerTransformer;
use DeskPRO\Bundle\ApiBundle\DataSerializer\DataTransformerRequest;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;

class AgentChatTransformer extends AbstractDataSerializerTransformer
{
    public function getAutomaticProperties(DataTransformerRequest $transformation_request)
    {
        return [
            'id',
            'date_created',
            'date_last_message',
        ];
    }

    public function getCustomProperties(DataTransformerRequest $transformation_request)
    {
        /** @var AgentChat $entity */
        $entity = $transformation_request->getDataToBeTransformed();
        $persons = $entity->getPersonList();
        $participants = [];
        foreach($persons as $person) {
            $participants[$person->getId()] = [
                'id' => $person->getId(),
                'avatar' => $person->getGravatarUrl(),
                'name' => $person->getDisplayName(),
            ];
        }
        return [
            'participants' => $participants,
        ];
    }
}
