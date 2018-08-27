<?php

namespace DeskPRO\Bundle\VoiceBundle\Twilio;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use DeskPRO\Bundle\AppBundle\Entity\VoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoiceNumber;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallParticipantUser;
use DeskPRO\Bundle\AppBundle\Settings\VoiceSettingsResolver;
use DeskPRO\Bundle\VoiceBundle\Twilio\Model\TwilioAvailableNumber;
use DeskPRO\Bundle\VoiceBundle\Twilio\Model\TwilioExistingNumber;
use DeskPRO\Bundle\VoiceBundle\Twilio\Model\TwilioPaginate;
use DeskPRO\Bundle\VoiceBundle\Twilio\Rest\Proxy\ClientProxy;
use Doctrine\ORM\EntityManager;
use Orb\Util\Strings;
use Twilio\Exceptions\TwilioException;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\ClientToken;
use Twilio\Jwt\Grants\VoiceGrant;
use Twilio\Rest\Api\V2010\Account\IncomingPhoneNumberInstance;
use Twilio\Rest\Client;
use Twilio\Values;

/**
 * Class TwilioAdapter.
 */
class TwilioAdapter
{
    const VOICEMAIL_WAITING_TIMEOUT = 30;
    const WORKFLOW_NAME             = 'DeskPRO Queue Routing Workflow';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var VoiceSettingsResolver
     */
    private $settingsResolver;

    /**
     * @var array
     */
    private $activities;

    /**
     * @var array
     */
    private $cache = [];

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
     * @param AgentData $agentData
     *
     * @return string
     */
    public static function getActivityStatus(AgentData $agentData)
    {
        $status = $agentData->getAvailableStatus();
        if (!$agentData->isAgentCallsEnabled()) {
            $status = AgentData::AVAILABLE_STATUS_OFFLINE;
        }

        return ucfirst(Strings::dashToCamelCase($status));
    }

    /**
     * @param VoiceAccount $account
     *
     * @return \Twilio\Rest\Api\V2010\AccountInstance|false
     */
    public function getAccount(VoiceAccount $account)
    {
        $accountSid = $account->getAccountSid();
        if (!isset($this->cache['account'][$accountSid])) {
            try {
                $value = $this->getClient($account)->getAccount()->fetch();
            } catch (\Exception $e) {
                $value = false;
            }

            $this->cache['account'][$accountSid] = $value;
        }

        return $this->cache['account'][$accountSid];
    }

    /**
     * @param VoiceAccount $account
     * @param string       $countryCode
     * @param string       $type
     * @param array        $options
     *
     * @return TwilioAvailableNumber[]
     */
    public function getAvailablePhoneNumbers(VoiceAccount $account, $countryCode, $type, array $options)
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
     * @param VoiceAccount $account
     * @param int          $pageNum
     *
     * @return TwilioPaginate
     */
    public function getExistingPhoneNumbers(VoiceAccount $account, $pageNum = 1)
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
     * @param VoiceAccount $account
     * @param array        $data
     *
     * @return bool|IncomingPhoneNumberInstance
     */
    public function buyNumber(VoiceAccount $account, array $data)
    {
        return $this->getClient($account)->incomingPhoneNumbers->create($data);
    }

    /**
     * @param VoiceNumber $number
     * @param array       $options
     *
     * @throws TwilioException
     */
    public function updateNumber(VoiceNumber $number, array $options)
    {
        $account = $number->getAccount();
        if (!$account) {
            throw new TwilioException('Voice number does not have an account reference.');
        }

        $this->getClient($account)->incomingPhoneNumbers($number->getSid())->update($options);
    }

    /**
     * @param VoiceAccount $account
     * @param string       $requestUrl
     * @param string       $voiceMethod
     * @param string       $statusUrl
     * @param string       $statusMethod
     *
     * @return \Twilio\Rest\Api\V2010\Account\ApplicationInstance
     */
    public function createTwimlApp(VoiceAccount $account, $requestUrl, $voiceMethod, $statusUrl, $statusMethod)
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
     * @param Person $person
     *
     * @return string
     */
    public static function getWorkerContactUrl(Person $person)
    {
        return 'client:'.self::getWorkerClientName($person);
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
     * @param VoiceAccount $account
     * @param Person       $person
     *
     * @return string
     */
    public function createAccessToken(VoiceAccount $account, Person $person)
    {
        $client = $this->getClient($account);
        $apiKey = $client->newKeys->create(['friendlyName' => "Agent {$person->getId()}"]);

        // create access token, which we will serialize and send to the client
        $token = new AccessToken(
            $account->getAccountSid(),
            $apiKey->sid,
            $apiKey->secret,
            3600,
            self::getWorkerClientName($person)
        );

        // create Voice grant
        $voiceGrant = new VoiceGrant();
        $voiceGrant->setOutgoingApplicationSid($account->getTwimlAppSid());

        $token->addGrant($voiceGrant);

        return $token->toJWT();
    }

    /**
     * @param VoiceAccount $account
     * @param Person       $person
     *
     * @return string|null
     */
    public function createPhoneToken(VoiceAccount $account, Person $person)
    {
        $capability = new ClientToken($account->getAccountSid(), $account->getAuthToken());
        $capability->allowClientOutgoing($account->getTwimlAppSid());
        $capability->allowClientIncoming(self::getWorkerClientName($person));

        $token = $capability->generateToken(28800);

        return $token;
    }

    /**
     * @param VoiceAccount $account
     * @param Person       $person
     */
    public function cancelForwardingCall(VoiceAccount $account, Person $person)
    {
        $agentData = $person->getAgentData();
        if (!$agentData || !$agentData->getForwardingNumber()) {
            return;
        }

        $forwardingCalls = $this->getClient($account)->calls->read([
            'to'     => $agentData->getForwardingNumber(),
            'status' => 'ringing',
        ]);

        foreach ($forwardingCalls as $forwardingCall) {
            try {
                $forwardingCall->update([
                    'status' => 'canceled',
                ]);
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * @param VoicePhoneCall $phoneCall
     */
    public function cancelForwardingCalls(VoicePhoneCall $phoneCall)
    {
        $account = $phoneCall->getNumber()->getAccount();
        $client  = $this->getClient($account);

        foreach ($phoneCall->getForwardingSids() as $forwardingSid) {
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
     * @param VoiceAccount $account
     * @param string       $conferenceSid
     *
     * @return \Twilio\Rest\Api\V2010\Account\ConferenceInstance
     */
    public function getConference(VoiceAccount $account, $conferenceSid)
    {
        return $this->getConferenceContext($account, $conferenceSid)->fetch();
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param string         $callSid
     * @param bool           $mute
     */
    public function muteParticipant(VoicePhoneCall $phoneCall, $callSid, $mute)
    {
        $account      = $phoneCall->getNumber()->getAccount();
        $participants = $this->getConferenceParticipants($account, $phoneCall->getConferenceSid());

        foreach ($participants as $participant) {
            if ($participant->callSid === $callSid) {
                $participant->update([
                    'muted' => $mute ? 'true' : 'false',
                ]);
            }
        }

        unset($this->cache[$phoneCall->getConferenceSid()]['participants']);
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param bool           $isHold
     */
    public function holdConferenceEndUser(VoicePhoneCall $phoneCall, $isHold)
    {
        $account      = $phoneCall->getNumber()->getAccount();
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

        unset($this->cache[$phoneCall->getConferenceSid()]['participants']);
    }

    /**
     * @param VoicePhoneCall $phoneCall
     */
    public function tryEndConference(VoicePhoneCall $phoneCall)
    {
        $account      = $phoneCall->getNumber()->getAccount();
        $participants = $this->getConferenceParticipants($account, $phoneCall->getConferenceSid());

        if (count($participants) < 2) {
            $userParticipants = $phoneCall->getUserParticipants()->map(function (VoicePhoneCallParticipantUser $participant) {
                return $participant->getCallSid();
            });

            foreach ($participants as $participant) {
                if ($userParticipants->contains($participant->callSid)) {
                    $participant->delete();
                }
            }
        }

        unset($this->cache[$phoneCall->getConferenceSid()]['participants']);
    }

    /**
     * @param VoicePhoneCall $phoneCall
     *
     * @return Person[]
     */
    public function getActivePhoneCallParticipants(VoicePhoneCall $phoneCall)
    {
        $account      = $phoneCall->getNumber()->getAccount();
        $participants = $this->getConferenceParticipants($account, $phoneCall->getConferenceSid());

        $agents = [];
        foreach ($participants as $participant) {
            $agent = $phoneCall->getPersonByCallSid($participant->callSid);
            if ($agent) {
                $agents[] = $agent;
            }
        }

        return $agents;
    }

    /**
     * @param VoicePhoneCall $phoneCall
     *
     * @return bool
     */
    public function isConferenceOnHold(VoicePhoneCall $phoneCall)
    {
        $account      = $phoneCall->getNumber()->getAccount();
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
     * @return \Twilio\Rest\Api\V2010\Account\CallInstance
     */
    public function callNumber(VoiceNumber $number, $toNumber, array $options = [])
    {
        $account = $number->getAccount();
        $client  = $this->getClient($account);

        return $client->calls->create($toNumber, $number->getNumber(), $options);
    }

    /**
     * @param VoiceAccount $account
     * @param string       $callSid
     *
     * @return \Twilio\Rest\Api\V2010\Account\CallInstance
     */
    public function cancelCall(VoiceAccount $account, $callSid)
    {
        return $this->getClient($account)->calls($callSid)->update([
            'status' => 'canceled',
        ]);
    }

    /**
     * @param VoiceAccount $account
     *
     * @return Client
     */
    protected function getClient(VoiceAccount $account)
    {
        $client = new ClientProxy($account->getAccountSid(), $account->getAuthToken());
        $client
            ->setProxyUsername($this->settingsResolver->getProxyUsername())
            ->setProxyPassword($this->settingsResolver->getProxyPassword())
            ->setApiProxyUrl($this->settingsResolver->getProxyApiUrl())
            ->setTaskRouterProxyUrl($this->settingsResolver->getProxyTaskRouterUrl())
            ->setAccountsProxyUrl($this->settingsResolver->getProxyAccountsUrl())
            ->setProxyPricingUrl($this->settingsResolver->getProxyPricingUrl())
        ;

        return $client;
    }

    /**
     * @param VoiceAccount $account
     *
     * @return string[]
     */
    protected function getAccountNumbersList(VoiceAccount $account)
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
     * @param VoiceAccount $account
     * @param string       $conferenceSid
     *
     * @return \Twilio\Rest\Api\V2010\Account\ConferenceContext
     */
    protected function getConferenceContext(VoiceAccount $account, $conferenceSid)
    {
        return $this->getClient($account)->conferences($conferenceSid);
    }

    /**
     * @param VoiceAccount $account
     * @param string       $conferenceSid
     *
     * @return \Twilio\Rest\Api\V2010\Account\Conference\ParticipantInstance[]
     */
    protected function getConferenceParticipants(VoiceAccount $account, $conferenceSid)
    {
        if (!isset($this->cache[$conferenceSid]['participants'])) {
            $conference   = $this->getConferenceContext($account, $conferenceSid);
            $participants = $conference->participants->read();

            $this->cache[$conferenceSid]['participants'] = $participants;
        }

        return $this->cache[$conferenceSid]['participants'];
    }
}
