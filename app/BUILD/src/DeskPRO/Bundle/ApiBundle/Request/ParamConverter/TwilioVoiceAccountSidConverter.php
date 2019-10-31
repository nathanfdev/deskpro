<?php

namespace DeskPRO\Bundle\ApiBundle\Request\ParamConverter;

use DeskPRO\Bundle\AppBundle\Entity\TwilioVoiceAccount;
use DeskPRO\Bundle\VoiceBundle\Settings\VoiceSettingsResolver;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TwilioVoiceAccountSidConverter.
 */
class TwilioVoiceAccountSidConverter implements ParamConverterInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var VoiceSettingsResolver
     */
    private $settingsResolver;

    /**
     * Constructor.
     *
     * @param EntityManager         $em
     * @param VoiceSettingsResolver $settingsResolver
     */
    public function __construct(EntityManager $em, VoiceSettingsResolver $settingsResolver)
    {
        $this->em               = $em;
        $this->settingsResolver = $settingsResolver;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $accountSid = $request->attributes->get($configuration->getName());
        $authToken  = $request->attributes->get($configuration->getOptions()['tokenParam']);

        if (!$accountSid || !$authToken) {
            throw new NotFoundHttpException();
        }

        if (is_numeric($accountSid)) {
            $account = $this->em->getRepository(TwilioVoiceAccount::class)->findOneBy([
                'id'          => $accountSid,
                'accountAuth' => $authToken,
            ]);
        } else {
            $account = $this->em->getRepository(TwilioVoiceAccount::class)->findOneBy([
                'accountId'   => $accountSid,
                'accountAuth' => $authToken,
            ]);
        }

        // try to get from proxy
        if (!$account
            && $this->settingsResolver->getTwilioProxyUsername() === $accountSid
            && $this->settingsResolver->getTwilioProxyPassword() === $authToken
        ) {
            $account = $this->em->getRepository(TwilioVoiceAccount::class)->findOneBy([
                'accountId' => VoiceSettingsResolver::TWILIO_PROXY_ACCOUNT_PLACEHOLDER,
                'authToken' => '_',
            ]);
        }

        if (!$account) {
            throw new NotFoundHttpException();
        }

        $request->attributes->set($configuration->getName(), $account);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getConverter() === 'twilio_voice_account_sid';
    }
}
