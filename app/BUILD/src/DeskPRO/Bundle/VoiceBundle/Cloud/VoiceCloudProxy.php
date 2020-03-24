<?php

namespace DeskPRO\Bundle\VoiceBundle\Cloud;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Setting;
use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Entity\TwilioVoiceAccount;
use DeskPRO\Bundle\VoiceBundle\Exception\InsufficientBalanceException;
use DeskPRO\Bundle\VoiceBundle\Settings\VoiceSettingsResolver;
use Doctrine\ORM\EntityManager;
use DpSys\License;
use DpSys\LowError\SystemErrorHandler;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class VoiceCloudProxy.
 */
class VoiceCloudProxy
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var SettingsResolver
     */
    private $settingsResolver;

    /**
     * Constructor.
     *
     * @param EntityManager    $em
     * @param SettingsResolver $settingsResolver
     */
    public function __construct(EntityManager $em, SettingsResolver $settingsResolver)
    {
        $this->em               = $em;
        $this->settingsResolver = $settingsResolver;
    }

    /**
     * Get plivo proxy settings.
     *
     * @param Person $person
     *
     * @throws \RuntimeException
     */
    public function loadPlivoProxySettings(Person $person)
    {
        throw new \RuntimeException('Not supported');
    }

    /**
     * Get twilio proxy settings.
     *
     * @param Person $person
     *
     * @return array
     */
    public function loadTwilioProxySettings(Person $person)
    {
        if (defined('DPC_IS_CLOUD')) {
            return $this->callMemberAreaCloud($person);
        } else {
            return $this->callMemberAreaOnPrem($person);
        }
    }

    /**
     * Creates a proxy account if not exists.
     *
     * @param string $proxyUrl
     * @param string $accessToken
     * @param string $authToken
     *
     * @return TwilioVoiceAccount
     */
    public function createTwilioProxyAccount($proxyUrl, $accessToken, $authToken)
    {
        $apiHost     = "{$proxyUrl}/twilio/twilio-api-proxy/{$accessToken}/{$authToken}";
        $pricingHost = "{$proxyUrl}/twilio/twilio-pricing-proxy/{$accessToken}/{$authToken}";
        $clientHost  = "{$proxyUrl}/twilio/twilio-client/generate-token/{$accessToken}/{$authToken}";

        /** @var \Application\DeskPRO\EntityRepository\Setting $settingsRepo */
        $settingsRepo = $this->em->getRepository(Setting::class);
        $settingsRepo->updateSetting(VoiceSettingsResolver::VOICE_TWILIO_PROXY_API_HOST, $apiHost);
        $settingsRepo->updateSetting(VoiceSettingsResolver::VOICE_TWILIO_PROXY_PRICING_HOST, $pricingHost);
        $settingsRepo->updateSetting(VoiceSettingsResolver::VOICE_TWILIO_PROXY_CLIENT_HOST, $clientHost);
        $settingsRepo->updateSetting(VoiceSettingsResolver::VOICE_TWILIO_PROXY_USERNAME, $accessToken);
        $settingsRepo->updateSetting(VoiceSettingsResolver::VOICE_TWILIO_PROXY_PASSWORD, $authToken);

        // reload settings because we need these settings
        // to create account app in voice account doctrine listener
        $this->settingsResolver->getGlobalSettings(true);

        $account = $this->em->getRepository(TwilioVoiceAccount::class)->findOneBy([
            'accountId' => VoiceSettingsResolver::TWILIO_PROXY_ACCOUNT_PLACEHOLDER,
            'authToken' => '_',
        ]);

        if (!$account) {
            $account = new TwilioVoiceAccount();
            $account->setAccountId(VoiceSettingsResolver::TWILIO_PROXY_ACCOUNT_PLACEHOLDER);
            $account->setAuthToken('_');
            $account->setAccountName('Deskpro Voice Account');

            $this->em->persist($account);
            $this->em->flush();
        }

        return $account;
    }

    /**
     * Calls MA.
     *
     * @param Person $person
     * @param array  $data
     *
     * @throws \Exception
     *
     * @return array
     */
    private function callMemberAreaCloud(Person $person, array $data = [])
    {
        $accessToken = $this->settingsResolver->getGlobalSettings()->get('dpss.access_token');
        $authToken   = $this->settingsResolver->getGlobalSettings()->get('dpss.auth_token');
        $proxyUrl    = $this->settingsResolver->getGlobalSettings()->get('dpss.twilio_proxy_service_url');

        if ($accessToken && $authToken && $proxyUrl) {
            return [
                'accessToken'           => $accessToken,
                'authToken'             => $authToken,
                'twilioProxyServiceUrl' => $proxyUrl,
            ];
        }

        $tmpdata = new TmpData();
        $tmpdata->setType('dpc_init_ms_client_for_voice');
        $tmpdata->setData('person_id', $person->getId());
        $tmpdata->setData('person_name', $person->getName());
        $tmpdata->setData('person_email', $person->getPrimaryEmailAddress());
        $tmpdata->setData('data', $data);
        $tmpdata->setDateExpire(new \DateTime('+10 minutes'));

        $this->em->persist($tmpdata);
        $this->em->flush();

        $url = DP_MA_SERVER_SECURE.'/cloud/call/'.DPC_SITE_ID.'/'.$tmpdata->getCode();

        $client = new \Zend\Http\Client(null, ['timeout' => 15, 'sslverifypeer' => false]);
        $client->setMethod(\Zend\Http\Request::METHOD_GET);
        $client->setUri($url);

        $response = $client->send();
        $data     = json_decode($response->getBody(), true);

        if (!$data || !empty($data['error'])) {
            SystemErrorHandler::logException(new \Exception(json_encode($data)));

            throw new InsufficientBalanceException(@$data['code']);
        }

        if (empty($data['accessToken']) || empty($data['accessToken'])) {
            SystemErrorHandler::logException(new \Exception(json_encode($data)));

            throw new AccessDeniedException('dpms');
        }

        $data['twilioProxyServiceUrl'] = rtrim($data['twilioProxyServiceUrl'], '/');

        /** @var \Application\DeskPRO\EntityRepository\Setting $settingsRepo */
        $settingsRepo = $this->em->getRepository(Setting::class);
        $settingsRepo->updateSetting('dpss.access_token', $data['accessToken']);
        $settingsRepo->updateSetting('dpss.auth_token', $data['authToken']);
        $settingsRepo->updateSetting('dpss.twilio_proxy_service_url', $data['twilioProxyServiceUrl']);

        return $data;
    }

    /**
     * Calls MA.
     *
     * @param Person $person
     * @param array  $data
     *
     * @throws \Exception
     *
     * @return array
     */
    private function callMemberAreaOnPrem(Person $person, array $data = [])
    {
        $accessToken = $this->settingsResolver->getGlobalSettings()->get('dpss.access_token');
        $authToken   = $this->settingsResolver->getGlobalSettings()->get('dpss.auth_token');
        $proxyUrl    = $this->settingsResolver->getGlobalSettings()->get('dpss.twilio_proxy_service_url');

        if ($accessToken && $authToken && $proxyUrl) {
            return [
                'accessToken'           => $accessToken,
                'authToken'             => $authToken,
                'twilioProxyServiceUrl' => $proxyUrl,
            ];
        }

        // dpss hasn't been set up yet
        if (empty($accessToken) || empty($authToken)) {
            throw new InsufficientBalanceException('No DPSS access token configured', 402);
        }

        $url = sprintf(
            '%s/api/member-services-call/%s/%s/register-twilio',
            License::getSecureLicServer(),
            'LICENSE',
            License::getLicense()->getLicenseId()
        );

        $hdBaseUrl = $this->settingsResolver->getGlobalSettings()->get('dpss.callback_base_url')
            ?: $this->settingsResolver->getGlobalSettings()->get('core.deskpro_url');

        $client = new \Zend\Http\Client(null, ['timeout' => 15, 'sslverifypeer' => false]);
        $client->setAuth($accessToken, $authToken);
        $client->setMethod(\Zend\Http\Request::METHOD_POST);
        $client->setUri($url);
        $client->setParameterPost(['baseUrl' => $hdBaseUrl]);

        $response = $client->send();
        $data     = json_decode($response->getBody(), true);

        $data['twilioProxyServiceUrl'] = rtrim($data['twilioProxyServiceUrl'], '/');

        if (!$data || !empty($data['error']) || empty($data['twilioProxyServiceUrl'])) {
            SystemErrorHandler::logException(new \Exception(json_encode($data)), false, null, true);

            throw new InsufficientBalanceException(@$data['code']);
        }

        /** @var \Application\DeskPRO\EntityRepository\Setting $settingsRepo */
        $settingsRepo = $this->em->getRepository(Setting::class);
        $settingsRepo->updateSetting('dpss.twilio_proxy_service_url', $data['twilioProxyServiceUrl']);

        return array_merge([
            'accessToken' => $accessToken,
            'authToken'   => $authToken,
        ], $data);
    }
}
