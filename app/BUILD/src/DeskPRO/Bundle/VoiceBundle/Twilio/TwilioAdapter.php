<?php

namespace DeskPRO\Bundle\VoiceBundle\Twilio;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\TwilioVoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoiceNumber;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallParticipantUser;
use DeskPRO\Bundle\VoiceBundle\Settings\VoiceSettingsResolver;
use DeskPRO\Bundle\VoiceBundle\Twilio\Model\TwilioAvailableNumber;
use DeskPRO\Bundle\VoiceBundle\Twilio\Model\TwilioExistingNumber;
use DeskPRO\Bundle\VoiceBundle\Twilio\Model\TwilioPaginate;
use DeskPRO\Bundle\VoiceBundle\Twilio\Rest\Proxy\ClientProxy;
use DeskPRO\Bundle\VoiceBundle\VoiceProviderInterface;
use Doctrine\ORM\EntityManager;
use Twilio\Jwt\ClientToken;
use Twilio\Rest\Api\V2010\Account\IncomingPhoneNumberInstance;
use Twilio\Rest\Client;
use Twilio\Values;

/**
 * Class TwilioAdapter.
 */
class TwilioAdapter implements VoiceProviderInterface
{
    const VOICEMAIL_WAITING_TIMEOUT = 30;

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
     * @param TwilioVoiceAccount $account
     *
     * @return \Twilio\Rest\Api\V2010\AccountInstance|false
     */
    public function getAccount(TwilioVoiceAccount $account)
    {
        try {
            $value = $this->getClient($account)->getAccount()->fetch();
        } catch (\Exception $e) {
            $value = false;
        }

        return $value;
    }

    /**
     * @param TwilioVoiceAccount $account
     * @param string             $countryCode
     * @param string             $type
     * @param array              $options
     *
     * @return TwilioAvailableNumber[]
     */
    public function getAvailablePhoneNumbers(TwilioVoiceAccount $account, $countryCode, $type, array $options)
    {
        $numbers = [];

        try {
            $client  = $this->getClient($account);
            $result  = $client->availablePhoneNumbers($countryCode)->$type->page($options);
            $exclude = $this->getAccountNumbersList($account);
            $prices  = $client->pricing->phoneNumbers->countries($countryCode)->fetch();

            $priceTypeMap = [
                'local'     => 'local',
                'national'  => 'local',
                'mobile'    => 'mobile',
                'toll free' => 'tollFree',
            ];

            $pricesMap = [];
            foreach ($prices->phoneNumberPrices as $price) {
                $pricesMap[$priceTypeMap[$price['number_type']]] = $price['current_price'];
            }

            foreach ($result as $apiNumber) {
                $numbers[] = new TwilioAvailableNumber(
                    $apiNumber,
                    $account,
                    isset($exclude[$apiNumber->phoneNumber]),
                    $type,
                    $pricesMap[$type],
                    $prices->priceUnit
                );
            }
        } catch (\Exception $e) {
        }

        return $numbers;
    }

    /**
     * @param TwilioVoiceAccount $account
     * @param int                $pageNum
     *
     * @return TwilioPaginate
     */
    public function getExistingPhoneNumbers(TwilioVoiceAccount $account, $pageNum = 1)
    {
        try {
            $page = $this->getClient($account)->incomingPhoneNumbers->page([], Values::NONE, Values::NONE, $pageNum - 1);

            $exclude = $this->getAccountNumbersList($account);
            $numbers = [];
            foreach ($page as $apiNumber) {
                $numbers[] = new TwilioExistingNumber(
                    $apiNumber,
                    $account,
                    isset($exclude[$apiNumber->phoneNumber])
                );
            }

            return new TwilioPaginate($numbers, $pageNum, $page);
        } catch (\Exception $e) {
            return new TwilioPaginate([], $pageNum);
        }
    }

    /**
     * @param TwilioVoiceAccount $account
     * @param array              $data
     *
     * @return bool|IncomingPhoneNumberInstance
     */
    public function buyNumber(TwilioVoiceAccount $account, array $data)
    {
        return $this->getClient($account)->incomingPhoneNumbers->create($data);
    }

    /**
     * @param VoiceNumber $number
     *
     * @throws \Exception
     */
    public function setTwimlAppId(VoiceNumber $number)
    {
        $account = $number->getAccount();
        if (!$account || !$account instanceof TwilioVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        $this->getClient($account)->incomingPhoneNumbers($number->getSid())->update([
            'voiceApplicationSid' => $account->getTwimlAppSid(),
        ]);
    }

    /**
     * @param VoiceNumber $number
     *
     * @throws \Exception
     */
    public function unsetTwimlAppId(VoiceNumber $number)
    {
        $account = $number->getAccount();
        if (!$account || !$account instanceof TwilioVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        $this->getClient($account)->incomingPhoneNumbers($number->getSid())->update([
            'voiceApplicationSid' => '',
        ]);
    }

    /**
     * @param TwilioVoiceAccount $account
     * @param string             $requestUrl
     * @param string             $voiceMethod
     * @param string             $statusUrl
     * @param string             $statusMethod
     *
     * @return \Twilio\Rest\Api\V2010\Account\ApplicationInstance
     */
    public function createTwimlApp(TwilioVoiceAccount $account, $requestUrl, $voiceMethod, $statusUrl, $statusMethod)
    {
        $client  = $this->getClient($account);
        $appName = 'DeskPRO App';

        // ensure we don't have twiml app with this name
        foreach ($client->applications->read() as $existingApp) {
            if (strtolower($existingApp->friendlyName) === strtolower($appName)) {
                $existingApp->delete();
            }
        }

        // create twiml app
        $application = $client->applications->create($appName, [
            'voiceUrl'             => $requestUrl,
            'voiceMethod'          => $voiceMethod,
            'statusCallback'       => $statusUrl,
            'statusCallbackMethod' => $statusMethod,
        ]);

        return $application;
    }

    /**
     * @param Person $person
     *
     * @return string
     */
    public static function getWorkerClientName(Person $person)
    {
        return 'deskpro'.$person->getId();
    }

    /**
     * @param string $caller
     *
     * @return bool
     */
    public static function isWorkerContactUrl($caller)
    {
        return strpos($caller, 'client:') === 0;
    }

    /**
     * @param TwilioVoiceAccount $account
     * @param Person             $person
     *
     * @return string|null
     */
    public function createPhoneToken(TwilioVoiceAccount $account, Person $person)
    {
        $capability = new ClientToken($account->getAccountId(), $account->getAuthToken());
        $capability->allowClientOutgoing($account->getTwimlAppSid());
        $capability->allowClientIncoming(self::getWorkerClientName($person));

        $token = $capability->generateToken(604800);

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function cancelForwardingCall(VoicePhoneCall $phoneCall, Person $person)
    {
        $account = $phoneCall->getNumber()->getAccount();
        if (!$account || !$account instanceof TwilioVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        $agentData = $person->getAgentData();
        if (!$agentData || !$agentData->getForwardingNumber()) {
            return;
        }

        $client = $this->getClient($account);
        foreach ($phoneCall->getAgentForwardingSids($person->getId()) as $forwardingSid) {
            try {
                $forwardingCall = $client->calls($forwardingSid)->fetch();
                if ($forwardingCall->status === 'ringing') {
                    $forwardingCall->update([
                        'status' => 'canceled',
                    ]);
                }
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function cancelForwardingCalls(VoicePhoneCall $phoneCall)
    {
        $account = $phoneCall->getNumber()->getAccount();
        if (!$account || !$account instanceof TwilioVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        $client = $this->getClient($account);
        foreach ($phoneCall->getForwardingSids() as $agentId => $forwardingSids) {
            foreach ($forwardingSids as $forwardingSid) {
                try {
                    $forwardingCall = $client->calls($forwardingSid)->fetch();
                    if ($forwardingCall->status === 'ringing') {
                        $forwardingCall->update([
                            'status' => 'canceled',
                        ]);
                    }
                } catch (\Exception $e) {
                }
            }
        }
    }

    /**
     * @param TwilioVoiceAccount $account
     * @param string             $conferenceSid
     *
     * @return \Twilio\Rest\Api\V2010\Account\ConferenceInstance
     */
    public function getConference(TwilioVoiceAccount $account, $conferenceSid)
    {
        return $this->getConferenceContext($account, $conferenceSid)->fetch();
    }

    /**
     * {@inheritdoc}
     */
    public function muteParticipant(VoicePhoneCall $phoneCall, $callSid, $mute)
    {
        $account = $phoneCall->getNumber()->getAccount();
        if (!$account || !$account instanceof TwilioVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        $participants = $this->getConferenceParticipants($account, $phoneCall->getConferenceSid());

        foreach ($participants as $participant) {
            if ($participant->callSid === $callSid) {
                $participant->update([
                    'muted' => $mute ? 'true' : 'false',
                ]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function holdConferenceEndUser(VoicePhoneCall $phoneCall, $isHold)
    {
        $account = $phoneCall->getNumber()->getAccount();
        if (!$account || !$account instanceof TwilioVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        $participants = $this->getConferenceParticipants($account, $phoneCall->getConferenceSid());

        if (!$phoneCall->getUserParticipants()->count()) {
            return;
        }

        /** @var VoicePhoneCallParticipantUser $userParticipant */
        $userParticipant = $phoneCall->getUserParticipants()->first();

        foreach ($participants as $participant) {
            if ($participant->callSid === $userParticipant->getCallSid()) {
                $participant->update([
                    'hold' => $isHold ? 'true' : 'false',
                ]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function tryEndConference(VoicePhoneCall $phoneCall)
    {
        $account = $phoneCall->getNumber()->getAccount();
        if (!$account || !$account instanceof TwilioVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        $participants = $this->getConferenceParticipants($account, $phoneCall->getConferenceSid());

        if (count($participants) < 2) {
            $userParticipants = $phoneCall->getUserParticipants()->map(function (VoicePhoneCallParticipantUser $participant) {
                return $participant->getCallSid();
            });

            foreach ($participants as $participant) {
                if ($userParticipants->contains($participant->callSid)) {
                    try {
                        $participant->delete();
                    } catch (\Exception $e) {
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getActivePhoneCallParticipants(VoicePhoneCall $phoneCall)
    {
        $account = $phoneCall->getNumber()->getAccount();
        if (!$account || !$account instanceof TwilioVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        $agents = [];

        try {
            $participants = $this->getConferenceParticipants($account, $phoneCall->getConferenceSid());
            foreach ($participants as $participant) {
                $agent = $phoneCall->getPersonByCallSid($participant->callSid);
                if ($agent) {
                    $agents[] = $agent;
                }
            }
        } catch (\Exception $e) {
        }

        return $agents;
    }

    /**
     * {@inheritdoc}
     */
    public function isConferenceOnHold(VoicePhoneCall $phoneCall)
    {
        $account = $phoneCall->getNumber()->getAccount();
        if (!$account || !$account instanceof TwilioVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        $participants = $this->getConferenceParticipants($account, $phoneCall->getConferenceSid());
        foreach ($participants as $participant) {
            if ($participant->callSid === $phoneCall->getCallSid()) {
                return $participant->hold;
            }
        }

        return false;
    }

    /**
     * @param VoiceNumber $number
     * @param string      $toNumber
     * @param array       $options
     *
     * @throws \Exception
     *
     * @return \Twilio\Rest\Api\V2010\Account\CallInstance
     */
    public function callNumber(VoiceNumber $number, $toNumber, array $options = [])
    {
        $account = $number->getAccount();
        if (!$account || !$account instanceof TwilioVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        $client = $this->getClient($account);

        return $client->calls->create($toNumber, $number->getNumber(), $options);
    }

    /**
     * {@inheritdoc}
     */
    public function isCallActive(VoicePhoneCall $phoneCall)
    {
        $account = $phoneCall->getNumber()->getAccount();
        if (!$account || !$account instanceof TwilioVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        try {
            $call = $this->getClient($account)->calls($phoneCall->getCallSid())->fetch();
            if ($call) {
                return $call->status === 'in-progress';
            }
        } catch (\Exception $e) {
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function cancelCall(VoicePhoneCall $phoneCall)
    {
        $account = $phoneCall->getNumber()->getAccount();
        if (!$account || !$account instanceof TwilioVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        foreach ($phoneCall->getUserParticipants() as $participant) {
            try {
                $this->getClient($account)->calls($participant->getCallSid())->update([
                    'status' => 'canceled',
                ]);
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function transferCall(VoicePhoneCall $phoneCall, $callbackUrl, $callbackMethod)
    {
        $account = $phoneCall->getNumber()->getAccount();
        if (!$account || !$account instanceof TwilioVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        try {
            $this->getClient($account)->calls($phoneCall->getCallSid())->update([
                'url'    => $callbackUrl,
                'method' => $callbackMethod,
            ]);
        } catch (\Exception $e) {
        }
    }

    /**
     * @param TwilioVoiceAccount $account
     *
     * @return Client
     */
    protected function getClient(TwilioVoiceAccount $account)
    {
        $client = new ClientProxy($account->getAccountId(), $account->getAuthToken());
        $client
            ->setApiProxyUrl($this->settingsResolver->getTwilioProxyApiUrl())
            ->setTaskRouterProxyUrl($this->settingsResolver->getTwilioProxyTaskRouterUrl())
            ->setAccountsProxyUrl($this->settingsResolver->getTwilioProxyAccountsUrl())
            ->setProxyPricingUrl($this->settingsResolver->getTwilioProxyPricingUrl())
        ;

        return $client;
    }

    /**
     * @param TwilioVoiceAccount $account
     *
     * @return string[]
     */
    protected function getAccountNumbersList(TwilioVoiceAccount $account)
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('n.number')
            ->from(VoiceNumber::class, 'n')
            ->where('n.account = :account')
            ->setParameter('account', $account)
        ;

        $result  = $qb->getQuery()->getArrayResult();
        $numbers = [];

        foreach ($result as $number) {
            $numbers[] = $number['number'];
        }

        return array_fill_keys($numbers, true);
    }

    /**
     * @param TwilioVoiceAccount $account
     * @param string             $conferenceSid
     *
     * @return \Twilio\Rest\Api\V2010\Account\ConferenceContext
     */
    protected function getConferenceContext(TwilioVoiceAccount $account, $conferenceSid)
    {
        return $this->getClient($account)->conferences($conferenceSid);
    }

    /**
     * @param TwilioVoiceAccount $account
     * @param string             $conferenceSid
     *
     * @return \Twilio\Rest\Api\V2010\Account\Conference\ParticipantInstance[]
     */
    protected function getConferenceParticipants(TwilioVoiceAccount $account, $conferenceSid)
    {
        try {
            $conference   = $this->getConferenceContext($account, $conferenceSid);
            $participants = $conference->participants->read();

            return $participants;
        } catch (\Exception $e) {
            return [];
        }
    }
}
