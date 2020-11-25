<?php

namespace DeskPRO\Bundle\VoiceBundle\Twilio;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AbstractVoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\AbstractVoicePhoneCallParticipant;
use DeskPRO\Bundle\AppBundle\Entity\TwilioVoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoiceNumber;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallParticipantAgent;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCallParticipantUser;
use DeskPRO\Bundle\VoiceBundle\Exception\BlacklistException;
use DeskPRO\Bundle\VoiceBundle\Exception\InsufficientBalanceException;
use DeskPRO\Bundle\VoiceBundle\Model\BillingSummary\ProviderBillingSummaryRecord;
use DeskPRO\Bundle\VoiceBundle\Settings\VoiceSettingsResolver;
use DeskPRO\Bundle\VoiceBundle\Twilio\Model\TwilioAvailableNumber;
use DeskPRO\Bundle\VoiceBundle\Twilio\Model\TwilioCountry;
use DeskPRO\Bundle\VoiceBundle\Twilio\Model\TwilioExistingNumber;
use DeskPRO\Bundle\VoiceBundle\Twilio\Rest\Proxy\CallContextProxy;
use DeskPRO\Bundle\VoiceBundle\Twilio\Rest\Proxy\ClientProxy;
use DeskPRO\Bundle\VoiceBundle\VoiceProviderInterface;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Psr7\Request as GuzzleHttpRequest;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twilio\Exceptions\RestException;
use Twilio\Jwt\ClientToken;
use Twilio\Rest\Api\V2010\Account\CallInstance;
use Twilio\Rest\Api\V2010\Account\IncomingPhoneNumberInstance;
use Twilio\Rest\Client;

/**
 * Class TwilioAdapter.
 */
class TwilioAdapter implements VoiceProviderInterface
{
    const VOICEMAIL_WAITING_TIMEOUT = 15;

    const ERROR_CODE_BLACK_LIST = 21216;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var VoiceSettingsResolver
     */
    private $voiceSettingsResolver;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param EntityManager         $em
     * @param VoiceSettingsResolver $voiceSettingsResolver
     * @param UrlGeneratorInterface $router
     * @param LoggerInterface       $logger
     */
    public function __construct(
        EntityManager $em,
        VoiceSettingsResolver $voiceSettingsResolver,
        UrlGeneratorInterface $router,
        LoggerInterface $logger
    ) {
        $this->em                    = $em;
        $this->voiceSettingsResolver = $voiceSettingsResolver;
        $this->router                = $router;
        $this->logger                = $logger;
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
     *
     * @return array
     */
    public function getAvailableCountries(TwilioVoiceAccount $account)
    {
        $counties = [];

        try {
            $client = $this->getClient($account);
            foreach ($client->availablePhoneNumbers->read() as $apiCountry) {
                if (!$apiCountry->beta) {
                    $counties[] = new TwilioCountry($apiCountry);
                }
            }
        } catch (\Exception $e) {
        }

        return $counties;
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
            $types   = $this->getAvailableNumberTypes($account, $countryCode);

            foreach ($result as $apiNumber) {
                $numbers[] = new TwilioAvailableNumber(
                    $apiNumber,
                    $account,
                    isset($exclude[$apiNumber->phoneNumber]),
                    $type,
                    isset($types[$type]) ? $types[$type] : '-',
                    $prices->priceUnit
                );
            }
        } catch (\Exception $e) {
        }

        return $numbers;
    }

    /**
     * @param TwilioVoiceAccount $account
     * @param string             $countryCode
     *
     * @return array
     */
    public function getAvailableNumberTypes(TwilioVoiceAccount $account, $countryCode)
    {
        $priceTypeMap = [
            'local'     => 'local',
            'national'  => 'local',
            'mobile'    => 'mobile',
            'toll free' => 'tollFree',
        ];

        try {
            $client = $this->getClient($account);
            $prices = $client->pricing->phoneNumbers->countries($countryCode)->fetch();

            $pricesMap = [];
            foreach ($prices->phoneNumberPrices as $price) {
                $pricesMap[$priceTypeMap[$price['number_type']]] = $price['current_price'];
            }

            return $pricesMap;
        } catch (\Exception $e) {
        }

        return [];
    }

    /**
     * @param TwilioVoiceAccount $account
     *
     * @return TwilioExistingNumber[]
     */
    public function getExistingPhoneNumbers(TwilioVoiceAccount $account)
    {
        try {
            $page = $this->getClient($account)->incomingPhoneNumbers->read();

            $exclude = $this->getAccountNumbersList($account);
            $numbers = [];
            foreach ($page as $apiNumber) {
                $numbers[] = new TwilioExistingNumber(
                    $apiNumber,
                    $account,
                    isset($exclude[$apiNumber->phoneNumber])
                );
            }

            return $numbers;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * @param TwilioVoiceAccount $account
     * @param array              $data
     *
     * @throws \Exception
     * @throws \Twilio\Exceptions\ConfigurationException
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
     * @throws \Exception
     * @throws \Twilio\Exceptions\ConfigurationException
     *
     * @return \Twilio\Rest\Api\V2010\Account\ApplicationInstance
     */
    public function createOrUpdateTwimlApp(TwilioVoiceAccount $account, $requestUrl, $voiceMethod, $statusUrl, $statusMethod)
    {
        $client  = $this->getClient($account);
        $appName = 'Deskpro Agent App';

        // ensure we don't have twiml app with this name
        foreach ($client->applications->read() as $existingApp) {
            if (strtolower($existingApp->friendlyName) === strtolower($appName)) {
                $existingApp->delete();
            }
        }

        // create twiml app
        return $client->applications->create($appName, [
            'voiceUrl'             => $requestUrl,
            'voiceMethod'          => $voiceMethod,
            'statusCallback'       => $statusUrl,
            'statusCallbackMethod' => $statusMethod,
        ]);
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
        $twimlAppSid = $account->getTwimlAppSid();
        $clientName  = self::getWorkerClientName($person);

        if (!$twimlAppSid) {
            return;
        }

        if ($this->voiceSettingsResolver->getTwilioProxyClientUrl()) {
            $client = new GuzzleHttpClient();
            $token  = $client->send(new GuzzleHttpRequest('GET', $this->voiceSettingsResolver->getTwilioProxyClientUrl()."/{$twimlAppSid}/{$clientName}"))->getBody()->getContents();
        } else {
            $capability = new ClientToken($account->getAccountId(), $account->getAuthToken());
            $capability->allowClientOutgoing($twimlAppSid);
            $capability->allowClientIncoming($clientName);

            $token = $capability->generateToken(604800);
        }

        return $token;
    }

    /**
     * @param AbstractVoiceAccount $account
     * @param string               $sid
     */
    public function releaseNumber(TwilioVoiceAccount $account, $sid)
    {
        try {
            $this->getClient($account)->incomingPhoneNumbers($sid)->delete();
        } catch (\Exception $e) {
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
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
        foreach ($phoneCall->getParticipantForwardedCallSids($person->getId()) as $callSid) {
            try {
                $client->calls($callSid)->update([
                    'status' => 'completed',
                ]);
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function endCall(VoicePhoneCall $phoneCall)
    {
        $account = $phoneCall->getNumber()->getAccount();
        if (!$account || !$account instanceof TwilioVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        $client = $this->getClient($account);
        foreach ($phoneCall->getFlattenCallSids() as $callSid) {
            try {
                $client->calls($callSid)->update([
                    'status' => 'completed',
                ]);
            } catch (\Exception $e) {
                $this->logger->error(sprintf(
                    '[TwilioAdapter] Unable to cancel call, call_sid = %s, reason = %s',
                    $callSid, $e->getMessage()
                ));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function holdEndUser(VoicePhoneCall $phoneCall, $isHold)
    {
        $account = $phoneCall->getNumber()->getAccount();
        if (!$account || !$account instanceof TwilioVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        if (!$phoneCall->getUserParticipants()->count()) {
            return;
        }

        /** @var VoicePhoneCallParticipantUser $userParticipant */
        $userParticipant = $phoneCall->getUserParticipants()->first();
        /** @var VoicePhoneCallParticipantAgent $userParticipant */
        $agentParticipant = $phoneCall->getAgentParticipants()->first();

        $holdUrl = $this->router->generate('twilio_put_on_hold_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'phoneCall'   => $phoneCall->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $unholdUrl = $this->router->generate('twilio_unhold_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'phoneCall'   => $phoneCall->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        if ($phoneCall->getConferenceSid()) {
            $conferenceParticipants = $this->getConferenceParticipants($account, $phoneCall->getConferenceSid());
            foreach ($conferenceParticipants as $participant) {
                if ($participant->callSid === $userParticipant->getCallSid()) {
                    $participant->update([
                        'hold' => $isHold ? 'true' : 'false',
                    ]);
                }
            }
        } else {
            if ($phoneCall->enqueuedAsAgent()) {
                if ($isHold) {
                    $this->transferParticipant($agentParticipant, $holdUrl, 'POST');
                } else {
                    $this->transferParticipant($userParticipant, $unholdUrl, 'POST');
                }
            } else {
                if ($isHold) {
                    $this->transferParticipant($userParticipant, $holdUrl, 'POST');
                } else {
                    $this->transferParticipant($agentParticipant, $unholdUrl, 'POST');
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function kickParticipant(AbstractVoicePhoneCallParticipant $participant)
    {
        $phoneCall = $participant->getPhoneCall();
        $account   = $phoneCall->getNumber()->getAccount();

        if (!$account instanceof TwilioVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        $client = $this->getClient($account);
        foreach ($phoneCall->getParticipantCallSids($participant->getPerson()->getId()) as $callSid) {
            try {
                $client->calls($callSid)->update([
                    'status' => 'completed',
                ]);
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function callNumber(VoicePhoneCall $phoneCall, $toNumber, array $options = [], &$exception = false)
    {
        $account = $phoneCall->getNumber()->getAccount();
        if (!$account || !$account instanceof TwilioVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        try {
            $call = $this->getClient($account)->calls->create($toNumber, $phoneCall->getNumber()->getNumber(), $options);

            return $call->sid;
        } catch (RestException $e) {
            if ($e->getCode() === self::ERROR_CODE_BLACK_LIST) {
                $exception = new BlacklistException();
            } elseif ($e->getStatusCode() === Response::HTTP_PAYMENT_REQUIRED) {
                $exception = new InsufficientBalanceException();
            } else {
                $exception = $e;
            }
        } catch (\Exception $e) {
            $exception = $e;

            return false;
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function callForwardingNumber(VoicePhoneCall $phoneCall, Person $agent)
    {
        if (!$agent->getForwardingNumber()) {
            return false;
        }

        $account = $phoneCall->getNumber()->getAccount();
        if (!$account || !$account instanceof TwilioVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        $forwardingUrl = $this->router->generate('twilio_answer_forwarding_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'CallId'      => $phoneCall->getId(),
            'AgentId'     => $agent->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $statusCallback = $this->router->generate('twilio_phone_number_status_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'callId'      => $phoneCall->getId(),
            'agentId'     => $agent->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $options = [
            'url'                  => $forwardingUrl,
            'method'               => 'POST',
            'statusCallback'       => $statusCallback,
            'statusCallbackMethod' => 'POST',
            'timeout'              => $agent->getAgentData()->getForwardingRingTimeout() ?: 10,
        ];

        if ($this->voiceSettingsResolver->getForwardingMachineDetection()) {
            $options = array_merge($options, [
                'machineDetection'                   => 'Enable',
                'machineDetectionSilenceTimeout'     => 2000,
                'machineDetectionSpeechThreshold'    => 1000,
                'machineDetectionSpeechEndThreshold' => 500,
                'machineDetectionTimeout'            => 3,
            ]);
        }

        $forwardedPhoneCall = $phoneCall;

        if ($this->voiceSettingsResolver->getForwardingNumberType() === VoiceSettingsResolver::SPECIFIC_FORWARDING_NUMBER
            && $this->voiceSettingsResolver->getForwardingNumber()
        ) {
            $voiceNumber = $this->em->getRepository(VoiceNumber::class)->find($this->voiceSettingsResolver->getForwardingNumber());
            if ($voiceNumber) {
                $forwardedPhoneCall = new VoicePhoneCall();
                $forwardedPhoneCall->setNumber($voiceNumber);
            }
        }

        return $this->callNumber($forwardedPhoneCall, $agent->getForwardingNumber(), $options);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
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
     *
     * @throws \Exception
     */
    public function transferParticipant(AbstractVoicePhoneCallParticipant $participant, $callbackUrl, $callbackMethod)
    {
        $account = $participant->getPhoneCall()->getNumber()->getAccount();
        if (!$account || !$account instanceof TwilioVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        try {
            $this->getClient($account)->calls($participant->getCallSid())->update([
                'url'    => $callbackUrl,
                'method' => $callbackMethod,
            ]);
        } catch (\Exception $e) {
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public function prepareForColdTransfer(VoicePhoneCall $phoneCall)
    {
        $this->holdEndUser($phoneCall, true);

        foreach ($phoneCall->getAgentParticipants() as $participant) {
            $this->kickParticipant($participant);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function deleteRecording(VoicePhoneCall $phoneCall, $recordingSid)
    {
        $account = $phoneCall->getNumber()->getAccount();
        if (!$account || !$account instanceof TwilioVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        try {
            $this->getClient($account)->recordings($recordingSid)->delete();
        } catch (\Exception $e) {
        }
    }

    /**
     * @param TwilioVoiceAccount $account
     * @param string             $callSid
     * @param bool               $initial
     *
     * @throws \Exception
     *
     * @return CallInstance|null
     */
    public function getCallInfo(TwilioVoiceAccount $account, $callSid, $initial)
    {
        try {
            $context = $this->getClient($account)->calls($callSid);
            if ($context instanceof CallContextProxy) {
                return $context->fetch($initial);
            }

            return $context->fetch();
        } catch (\Exception $e) {
        }

        return;
    }

    /**
     * @param TwilioVoiceAccount $account
     * @param \DateTime          $startDate
     * @param \DateTime          $endDate
     * @param array              $categories
     *
     * @return \Twilio\Rest\Api\V2010\Account\Usage\RecordInstance[]
     */
    public function getUsage(TwilioVoiceAccount $account, \DateTime $startDate, \DateTime $endDate, array $categories = [])
    {
        try {
            $options = [
                'startDate'          => $startDate,
                'endDate'            => $endDate,
                'includeSubaccounts' => false,
            ];

            $records = $this->getClient($account)->usage->records->read($options);
            $result  = [];

            foreach ($records as $record) {
                if (($categories && in_array($record->category, $categories)) || !$categories) {
                    $result[] = new ProviderBillingSummaryRecord($record);
                }
            }

            return $result;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * @param TwilioVoiceAccount $account
     *
     * @throws \Twilio\Exceptions\ConfigurationException
     *
     * @return Client
     */
    protected function getClient(TwilioVoiceAccount $account)
    {
        $client = new ClientProxy($account->getAccountId(), $account->getAuthToken());
        if ($account->isManagedAccount()) {
            // only use proxy for managed accounts
            $client
                ->setApiProxyUrl($this->voiceSettingsResolver->getTwilioProxyApiUrl())
                ->setTaskRouterProxyUrl($this->voiceSettingsResolver->getTwilioProxyTaskRouterUrl())
                ->setAccountsProxyUrl($this->voiceSettingsResolver->getTwilioProxyAccountsUrl())
                ->setProxyPricingUrl($this->voiceSettingsResolver->getTwilioProxyPricingUrl())
            ;
        }

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
     * @throws \Twilio\Exceptions\ConfigurationException
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
