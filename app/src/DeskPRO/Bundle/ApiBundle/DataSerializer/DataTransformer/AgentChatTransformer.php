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

class AgentChatTransformer extends AbstractDataSerializerTransformer
{
    public function getAutomaticProperties(DataTransformerRequest $transformation_request)
    {
        return [
            'id',
            'date_created',
            'date_last_message',
            'participants',
        ];
    }

    public function getCustomProperties(DataTransformerRequest $transformation_request)
    {
        return [];
    }
}
