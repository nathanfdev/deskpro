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

class AgentChatMessageTransformer extends AbstractDataSerializerTransformer
{
    public function getAutomaticProperties(DataTransformerRequest $transformation_request)
    {
        return [
            'id',
            'message',
            'metadata',
            'person_name',
            'agent_chat_id',
            'person_id',
            'date_created',
        ];
    }

    public function getCustomProperties(DataTransformerRequest $transformation_request)
    {
        return [];
    }
}
