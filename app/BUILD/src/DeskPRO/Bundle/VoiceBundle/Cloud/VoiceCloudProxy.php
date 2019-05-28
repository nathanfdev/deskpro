<?php

namespace DeskPRO\Bundle\VoiceBundle\Cloud;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Setting;
use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\VoiceBundle\Exception\InsufficientBalanceException;
use DeskPRO\Bundle\VoiceBundle\Settings\VoiceSettingsResolver;
use Doctrine\ORM\EntityManager;
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
        $data = $this->callMemberArea($person);

        /** @var \Application\DeskPRO\EntityRepository\Setting $settingsRepo */
        $settingsRepo = $this->em->getRepository(Setting::class);
        $settingsRepo->updateSetting(VoiceSettingsResolver::VOICE_PLIVO_PROXY_HOST, "{$data['dpmsUrl']}/voice/plivo-api/");
        $settingsRepo->updateSetting(VoiceSettingsResolver::VOICE_PLIVO_PROXY_USERNAME, $data['accessToken']);
        $settingsRepo->updateSetting(VoiceSettingsResolver::VOICE_PLIVO_PROXY_PASSWORD, $data['authToken']);
    }

    /**
     * Inits plivo proxy settings.
     *
     * @param Person $person
     */
    public function initTwilioProxy(Person $person)
    {
        $data = $this->callMemberArea($person);

        /** @var \Application\DeskPRO\EntityRepository\Setting $settingsRepo */
        $settingsRepo = $this->em->getRepository(Setting::class);
        $settingsRepo->updateSetting(VoiceSettingsResolver::VOICE_TWILIO_PROXY_HOST, "{$data['dpmsUrl']}/voice/twilio-api/");
        $settingsRepo->updateSetting(VoiceSettingsResolver::VOICE_TWILIO_PROXY_USERNAME, $data['accessToken']);
        $settingsRepo->updateSetting(VoiceSettingsResolver::VOICE_TWILIO_PROXY_PASSWORD, $data['authToken']);
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
    private function callMemberArea(Person $person, array $data = [])
    {
        $accessToken = $this->settingsResolver->getGlobalSettings()->get('dpms.access_token');
        $authToken   = $this->settingsResolver->getGlobalSettings()->get('dpms.auth_token');
        $dpmsUrl     = $this->settingsResolver->getGlobalSettings()->get('dpms.url');

        if ($accessToken && $authToken && $dpmsUrl) {
            return [
                'accessToken' => $accessToken,
                'authToken'   => $authToken,
                'dpmsUrl'     => $dpmsUrl,
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

        try {
            $client = new \Zend\Http\Client(null, ['timeout' => 15, 'sslverifypeer' => false]);
            $client->setMethod(\Zend\Http\Request::METHOD_GET);
            $client->setUri($url);
            $res = $client->send();

            $data = json_decode($res->getBody(), true);

            if (!empty($res['error'])) {
                throw new InsufficientBalanceException($res['code']);
            }

            if (empty($res['accessToken']) || empty($res['accessToken'])) {
                throw new AccessDeniedException('dpms');
            }

            /** @var \Application\DeskPRO\EntityRepository\Setting $settingsRepo */
            $settingsRepo = $this->em->getRepository(Setting::class);
            $settingsRepo->updateSetting('dpms.access_token', $data['accessToken']);
            $settingsRepo->updateSetting('dpms.auth_token', $data['authToken']);
            $settingsRepo->updateSetting('dpms.url', $data['dpmsUrl']);

            return $data;
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
            throw new AccessDeniedException('not_found', $e);
        }
    }
}
