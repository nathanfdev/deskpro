<?php

namespace DeskPRO\Bundle\VoiceBundle\Serializer\Handler\Entity;

use DeskPRO\Bundle\AppBundle\Entity\PlivoEndpoint as PlivoEndpointEntity;
use DeskPRO\Bundle\AppBundle\Entity\PlivoVoiceAccount;
use DeskPRO\Bundle\AppBundle\Serializer\Deferred\CallbackDeferredProperty;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\VoiceBundle\Serializer\Model\PlivoClientCredentials;
use DeskPRO\Bundle\VoiceBundle\Serializer\Model\PlivoEndpoint;
use DeskPRO\Bundle\VoiceBundle\Serializer\Model\PlivoVoiceAccount as PlivoVoiceAccountModel;
use Doctrine\ORM\EntityManager;

/**
 * Class PlivoVoiceAccountHandler.
 */
class PlivoVoiceAccountHandler extends AbstractEntityHandler
{
    /**
     * @var EntityManager
     */
    private $em;

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
        return PlivoVoiceAccount::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param PlivoVoiceAccount $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $model = new PlivoVoiceAccountModel($entity);
        $model->setClientCredentials(new CallbackDeferredProperty([$this, 'getClientCredentials'], [$entity, $context]));

        return $model;
    }

    /**
     * @param PlivoVoiceAccount            $entity
     * @param SideloadSerializationContext $context
     *
     * @return PlivoClientCredentials
     */
    public function getClientCredentials(PlivoVoiceAccount $entity, SideloadSerializationContext $context)
    {
        $endpoint = $this->em->getRepository(PlivoEndpointEntity::class)->findOneBy([
            'account' => $entity,
            'person'  => $context->getUser(),
        ]);

        $credentialsModel = new PlivoClientCredentials();
        if ($endpoint) {
            $credentialsModel->setEndpoint(new PlivoEndpoint($endpoint->getUsername(), $endpoint->getPassword()));
        }

        return $credentialsModel;
    }
}
