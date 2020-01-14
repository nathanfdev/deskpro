<?php

namespace DeskPRO\Bundle\VoiceBundle\Helper;

use DeskPRO\Bundle\AppBundle\Entity\VoiceAutoAttendant;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class TransferCallHelper.
 */
class TransferCallHelper
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var VoiceAssetHelper
     */
    private $voiceAssetHelper;

    /**
     * @var VoiceProviderHelper
     */
    private $voiceProviderHelper;

    /**
     * Constructor.
     *
     * @param EntityManager         $em
     * @param UrlGeneratorInterface $router
     * @param VoiceAssetHelper      $voiceAssetHelper
     * @param VoiceProviderHelper   $voiceProviderHelper
     */
    public function __construct(
        EntityManager $em,
        UrlGeneratorInterface $router,
        VoiceAssetHelper $voiceAssetHelper,
        VoiceProviderHelper $voiceProviderHelper
    ) {
        $this->em                  = $em;
        $this->router              = $router;
        $this->voiceAssetHelper    = $voiceAssetHelper;
        $this->voiceProviderHelper = $voiceProviderHelper;
    }

    /**
     * @param VoicePhoneCall $phoneCall
     */
    public function transferToVoicemail(VoicePhoneCall $phoneCall)
    {
        $account = $phoneCall->getNumber()->getAccount();

        if ($this->voiceAssetHelper->isVoicemailEnabled($phoneCall->getTaskSid())) {
            $asset        = $this->voiceAssetHelper->getVoicemailAsset($phoneCall->getTaskSid());
            $voicemailUrl = $this->router->generate($account->getRouterPrefix().'_voicemail', [
                'account'     => $account->getId(),
                'accountAuth' => $account->getAccountAuth(),
                'asset'       => $asset ? $asset->getId() : null,
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        } else {
            $asset        = $this->voiceAssetHelper->getVoicemailDisabledAsset($phoneCall->getTaskSid());
            $voicemailUrl = $this->router->generate($account->getRouterPrefix().'_voicemail_disabled', [
                'account'     => $account->getId(),
                'accountAuth' => $account->getAccountAuth(),
                'asset'       => $asset ? $asset->getId() : null,
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $this->voiceProviderHelper->transferUser($phoneCall, $voicemailUrl, 'POST');
    }

    /**
     * @param VoicePhoneCall $phoneCall
     */
    public function transferToTaskRouter(VoicePhoneCall $phoneCall)
    {
        $account   = $phoneCall->getNumber()->getAccount();
        $routerUrl = $this->router->generate($account->getRouterPrefix().'_call_routing_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'phoneCall'   => $phoneCall->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->voiceProviderHelper->transferUser($phoneCall, $routerUrl, 'POST');
    }

    /**
     * @param VoicePhoneCall     $phoneCall
     * @param VoiceAutoAttendant $autoAttendant
     */
    public function transferToAutoAttendant(VoicePhoneCall $phoneCall, VoiceAutoAttendant $autoAttendant)
    {
        $account   = $phoneCall->getNumber()->getAccount();
        $routerUrl = $this->router->generate($account->getRouterPrefix().'_auto_attendant_callback', [
            'account'       => $account->getId(),
            'accountAuth'   => $account->getAccountAuth(),
            'autoAttendant' => $autoAttendant->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->voiceProviderHelper->transferUser($phoneCall, $routerUrl, 'POST');
    }
}
