<?php

namespace DeskPRO\Bundle\VoiceBundle\Cloud;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Setting;
use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\NewSettings\SettingsResolver;
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
     * Inits plivo proxy settings.
     *
     * @param Person $person
     */
    public function initPlivoProxy(Person $person)
    {
        throw new \RuntimeException('Not supported');
    }

    /**
     * Inits plivo proxy settings.
     *
     * @param Person $person
     */
    public function initTwilioProxy(Person $person)
    {
        if (defined('DPC_IS_CLOUD')) {
            $data = $this->callMemberAreaCloud($person);
        } else {
            $data = $this->callMemberAreaOnPrem($person);
        }

        $apiHost     = "{$data['twilioProxyServiceUrl']}/twilio/twilio-api-proxy/{$data['accessToken']}/{$data['authToken']}";
        $pricingHost = "{$data['twilioProxyServiceUrl']}/twilio/twilio-pricing-proxy/{$data['accessToken']}/{$data['authToken']}";
        $clientHost  = "{$data['twilioProxyServiceUrl']}/twilio/twilio-client/generate-token/{$data['accessToken']}/{$data['authToken']}";

        /** @var \Application\DeskPRO\EntityRepository\Setting $settingsRepo */
        $settingsRepo = $this->em->getRepository(Setting::class);
        $settingsRepo->updateSetting(VoiceSettingsResolver::VOICE_TWILIO_PROXY_API_HOST, $apiHost);
        $settingsRepo->updateSetting(VoiceSettingsResolver::VOICE_TWILIO_PROXY_PRICING_HOST, $pricingHost);
        $settingsRepo->updateSetting(VoiceSettingsResolver::VOICE_TWILIO_PROXY_CLIENT_HOST, $clientHost);
        $settingsRepo->updateSetting(VoiceSettingsResolver::VOICE_TWILIO_PROXY_USERNAME, $data['accessToken']);
        $settingsRepo->updateSetting(VoiceSettingsResolver::VOICE_TWILIO_PROXY_PASSWORD, $data['authToken']);

        // reload settings because we need these settings
        // to create account app in voice account doctrine listener
        $this->settingsResolver->getGlobalSettings(true);
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
            DP_MA_SERVER_SECURE,
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
