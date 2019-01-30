<?php

namespace DeskPRO\Bundle\VoiceBundle\Helper;

use DeskPRO\Bundle\AppBundle\Entity\PlivoVoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\TwilioVoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAsset\AbstractVoiceAsset;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class VoicemailHelper.
 */
class VoicemailHelper
{
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
     * @param UrlGeneratorInterface $router
     * @param VoiceAssetHelper      $voiceAssetHelper
     * @param VoiceProviderHelper   $voiceProviderHelper
     */
    public function __construct(
        UrlGeneratorInterface $router,
        VoiceAssetHelper      $voiceAssetHelper,
        VoiceProviderHelper   $voiceProviderHelper
    ) {
        $this->router              = $router;
        $this->voiceAssetHelper    = $voiceAssetHelper;
        $this->voiceProviderHelper = $voiceProviderHelper;
    }

    /**
     * @param VoicePhoneCall $phoneCall
     */
    public function transferToVoicemail(VoicePhoneCall $phoneCall)
    {
        $this->voiceProviderHelper->transferCall(
            $phoneCall,
            $this->getVoicemailUrl(
                $phoneCall->getNumber()->getAccount(),
                $this->voiceAssetHelper->getVoicemailAsset($phoneCall->getTaskSid())
            ),
            'POST'
        );
    }

    /**
     * @param mixed              $account
     * @param AbstractVoiceAsset $asset
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    private function getVoicemailUrl($account, AbstractVoiceAsset $asset = null)
    {
        if ($account instanceof TwilioVoiceAccount) {
            $route = 'twilio_voicemail';
        } elseif ($account instanceof PlivoVoiceAccount) {
            $route = 'plivo_voicemail';
        } else {
            throw new \RuntimeException('Unknown account type');
        }

        return $this->router->generate($route, [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'asset'       => $asset ? $asset->getId() : null,
        ], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
