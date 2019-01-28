<?php

namespace DeskPRO\Bundle\VoiceBundle\Serializer\Handler\Entity;

use DeskPRO\Bundle\AppBundle\Entity\AbstractVoicePhoneCallParticipant;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallParticipantAgent;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallParticipantUser;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\VoiceBundle\Serializer\Model\VoicePhoneCallParticipant;

/**
 * Class VoicePhoneCallParticipantHandler.
 */
class VoicePhoneCallParticipantHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return [
            VoicePhoneCallParticipantAgent::class,
            VoicePhoneCallParticipantUser::class,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @param AbstractVoicePhoneCallParticipant $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        return new VoicePhoneCallParticipant($entity);
    }
}
