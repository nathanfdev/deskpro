<?php

namespace DeskPRO\Bundle\VoiceBundle\Serializer\Handler\Entity;

use DeskPRO\Bundle\AppBundle\Entity\TwilioVoiceAccount;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\VoiceBundle\Serializer\Model\TwilioClientCredentials;
use DeskPRO\Bundle\VoiceBundle\Serializer\Model\TwilioVoiceAccount as TwilioVoiceAccountModel;
use DeskPRO\Bundle\VoiceBundle\Twilio\TwilioAdapter;

/**
 * Class TwilioVoiceAccountHandler.
 */
class TwilioVoiceAccountHandler extends AbstractEntityHandler
{
    /**
     * @var TwilioAdapter
     */
    private $twilioAdapter;

    /**
     * Constructor.
     *
     * @param TwilioAdapter $twilioAdapter
     */
    public function __construct(TwilioAdapter $twilioAdapter)
    {
        $this->twilioAdapter = $twilioAdapter;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return TwilioVoiceAccount::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param TwilioVoiceAccount $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $model = new TwilioVoiceAccountModel($entity);
        $model->setClientCredentials(new CallbackDeferredProperty([$this, 'getClientCredentials'], [$entity, $context]));

        return $model;
    }

    /**
     * @param TwilioVoiceAccount           $entity
     * @param SideloadSerializationContext $context
     *
     * @return string
     */
    public function getClientCredentials(TwilioVoiceAccount $entity, SideloadSerializationContext $context)
    {
        $credentialsModel = new TwilioClientCredentials();
        $credentialsModel->setPhoneToken($this->twilioAdapter->createPhoneToken($entity, $context->getUser()));

        return $credentialsModel;
    }
}
