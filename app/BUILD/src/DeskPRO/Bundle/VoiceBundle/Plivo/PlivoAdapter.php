<?php

namespace DeskPRO\Bundle\VoiceBundle\Plivo;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AbstractVoicePhoneCallParticipant;
use DeskPRO\Bundle\AppBundle\Entity\PlivoVoiceAccount;
use DeskPRO\Bundle\AppBundle\Entity\VoiceNumber;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\VoiceBundle\Exception\InsufficientBalanceException;
use DeskPRO\Bundle\VoiceBundle\Exception\UnverifiedException;
use DeskPRO\Bundle\VoiceBundle\Plivo\Model\PlivoAvailableNumber;
use DeskPRO\Bundle\VoiceBundle\Plivo\Model\PlivoExistingNumber;
use DeskPRO\Bundle\VoiceBundle\Plivo\Model\PlivoPaginate;
use DeskPRO\Bundle\VoiceBundle\Plivo\Proxy\ProxyRestClient;
use DeskPRO\Bundle\VoiceBundle\Settings\VoiceSettingsResolver;
use DeskPRO\Bundle\VoiceBundle\VoiceProviderInterface;
use Doctrine\ORM\EntityManager;
use DpSys\LowError\SystemErrorHandler;
use Orb\Util\Strings;
use Plivo\Exceptions\PlivoNotFoundException;
use Plivo\Exceptions\PlivoResponseException;
use Plivo\Exceptions\PlivoRestException;
use Plivo\Resources\Application\ApplicationList;
use Plivo\Resources\Call\Call;
use Plivo\Resources\Call\CallCreateResponse;
use Plivo\Resources\Conference\ConferenceInterface;
use Plivo\Resources\Endpoint\Endpoint;
use Plivo\Resources\Number\Number;
use Plivo\Resources\PhoneNumber\PhoneNumber;
use Plivo\RestClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class PlivoAdapter.
 */
class PlivoAdapter implements VoiceProviderInterface
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
     * @param VoiceSettingsResolver $settingsResolver
     * @param UrlGeneratorInterface $router
     * @param LoggerInterface       $logger
     */
    public function __construct(
        EntityManager         $em,
        VoiceSettingsResolver $settingsResolver,
        UrlGeneratorInterface $router,
        LoggerInterface       $logger
    ) {
        $this->em               = $em;
        $this->settingsResolver = $settingsResolver;
        $this->router           = $router;
        $this->logger           = $logger;
    }

    /**
     * @param PlivoVoiceAccount $account
     * @param string            $appName
     * @param string            $answerUrl
     * @param string            $answerMethod
     * @param string            $hangupUrl
     * @param string            $hangupMethod
     *
     * @return string|null
     */
    public function createApplication(PlivoVoiceAccount $account, $appName, $answerUrl, $answerMethod, $hangupUrl, $hangupMethod)
    {
        try {
            $client = $this->getClient($account);

            // check for existing app with the same name
            // to avoid duplicate app name errors
            $existApplication = null;

            $limit  = 20;
            $offset = 0;

            do {
                /** @var ApplicationList $result */
                $result = $client->applications->getList([
                    'limit'  => $limit,
                    'offset' => $offset,
                ]);

                foreach ($result as $application) {
                    if ($this->normalizeAppName($application->appName) === $this->normalizeAppName($appName)) {
                        $existApplication = $application;
                        break;
                    }
                }

                if ($existApplication) {
                    break;
                }

                $offset += $limit;
            } while (isset($result->meta()['next']));

            $options = [
                'answer_url'    => $answerUrl,
                'answer_method' => $answerMethod,
                'hangup_url'    => $hangupUrl,
                'hangup_method' => $hangupMethod,
            ];

            // create or update plivo application
            if ($existApplication) {
                $client->applications->update($existApplication->appId, $options);

                return $existApplication->appId;
            } else {
                $result = $client->applications->create($appName, $options);

                return $result->appId;
            }
        } catch (PlivoRestException $e) {
            SystemErrorHandler::logException($e);

            return;
        }
    }

    private function normalizeAppName($name)
    {
        // [XXX::YYY] Deskpro Agent App -> deskpro agent app
        $name = preg_replace('/^\[[A-Z]+::[a-zA-Z0-9_\-]+\]\w*/', '', $name);
        $name = trim(strtolower($name));

        return $name;
    }

    /**
     * @param VoiceNumber $number
     *
     * @throws \RuntimeException
     */
    public function setApplicationId(VoiceNumber $number)
    {
        $account = $number->getAccount();
        if (!$account || !$account instanceof PlivoVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        $this->getClient($account)->numbers->update($number->getSid(), [
            'app_id' => $account->getUserApplicationId(),
        ]);
    }

    /**
     * @param VoiceNumber $number
     *
     * @throws \RuntimeException
     */
    public function unsetApplicationId(VoiceNumber $number)
    {
        $account = $number->getAccount();
        if (!$account || !$account instanceof PlivoVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        $this->getClient($account)->numbers->update($number->getSid(), [
            'app_id' => '',
        ]);
    }

    /**
     * @param PlivoVoiceAccount $account
     *
     * @return Endpoint[]
     */
    public function getEndpoints(PlivoVoiceAccount $account)
    {
        try {
            return $this->getClient($account)->endpoints->list();
        } catch (PlivoRestException $e) {
            return [];
        }
    }

    /**
     * @param PlivoVoiceAccount $account
     * @param Person            $person
     * @param string            $password
     *
     * @return Endpoint|null
     */
    public function createEndpoint(PlivoVoiceAccount $account, Person $person, $password)
    {
        try {
            $username = 'agent'.Strings::random(20);
            $alias    = self::getAgentEndpointAliases($person);

            return $this->getClient($account)->endpoints->create($username, $password, $alias, $account->getAgentApplicationId());
        } catch (PlivoRestException $e) {
            return;
        }
    }

    /**
     * @param PlivoVoiceAccount $account
     * @param string            $endpointId
     */
    public function deleteEndpoint(PlivoVoiceAccount $account, $endpointId)
    {
        try {
            $this->getClient($account)->endpoints->delete($endpointId);
        } catch (PlivoRestException $e) {
        }
    }

    /**
     * @param Person $person
     *
     * @return string
     */
    public static function getAgentEndpointAliases(Person $person)
    {
        return 'agent'.$person->getId();
    }

    /**
     * @return string
     */
    public static function createEndpointPassword()
    {
        return Strings::random(20);
    }

    /**
     * @param PlivoVoiceAccount $account
     *
     * @return \Plivo\Resources\Account\Account
     */
    public function getAccount(PlivoVoiceAccount $account)
    {
        // plivo sdk bugs out if the account is a sub-account because it tries to
        // use account_type which isnt set on sub-accounts, so a notice is raised
        return SystemErrorHandler::runWithoutErrorHandler(function () use ($account) {
            return $this->getClient($account)->accounts->get();
        });
    }

    /**
     * @param PlivoVoiceAccount $account
     * @param string            $conferenceName
     *
     * @return \Plivo\Resources\Conference\Conference|null
     */
    public function getConference(PlivoVoiceAccount $account, $conferenceName)
    {
        try {
            return $this->getClient($account)->getConferences()->get($conferenceName);
        } catch (PlivoRestException $e) {
            return;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function callNumber(VoicePhoneCall $phoneCall, $toNumber, array $options = [], &$exception = false)
    {
        $account = $phoneCall->getNumber()->getAccount();
        if (!$account instanceof PlivoVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        $answerUrl    = '';
        $answerMethod = 'POST';

        if (isset($options['answer_url'])) {
            $answerUrl = $options['answer_url'];
            unset($options['answer_url']);
        }
        if (isset($options['answer_method'])) {
            $answerMethod = $options['answer_method'];
            unset($options['answer_method']);
        }

        try {
            /** @var CallCreateResponse $call */
            $call = $this->getClient($account)->calls->create(
                $phoneCall->getNumber()->getNumber(),
                [$toNumber],
                $answerUrl,
                $answerMethod,
                $options
            );

            return $call->getRequestUuid();
        } catch (PlivoRestException $e) {
            if (strpos($e->getErrorMessage(), '"Destination Phone numbers need to be verified.') === 0) {
                $exception = new UnverifiedException();
            } elseif (strpos($e->getErrorMessage(), '"insufficient balance"') === 0) {
                $exception = new InsufficientBalanceException();
            } else {
                $exception = $e;
            }

            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function callForwardingNumber(VoicePhoneCall $phoneCall, Person $agent)
    {
        if (!$agent->getForwardingNumber()) {
            return false;
        }

        $account       = $phoneCall->getNumber()->getAccount();
        $forwardingUrl = $this->router->generate('plivo_answer_forwarding_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'CallId'      => $phoneCall->getId(),
            'AgentId'     => $agent->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->callNumber(
            $phoneCall,
            $agent->getForwardingNumber(),
            [
                'answer_url'        => $forwardingUrl,
                'machine_detection' => 'hangup',
                'ring_timeout'      => $agent->getAgentData()->getForwardingRingTimeout() ?: 10,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function cancelForwardingCall(VoicePhoneCall $phoneCall, Person $person)
    {
        $account = $phoneCall->getNumber()->getAccount();
        if (!$account instanceof PlivoVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        $agentData = $person->getAgentData();
        if (!$agentData || !$agentData->getForwardingNumber()) {
            return;
        }

        /** @var Call[] $forwardingCalls */
        $client = $this->getClient($account);
        foreach ($phoneCall->getParticipantForwardedCallSids($person->getId()) as $requestId) {
            try {
                $client->calls->cancel($requestId);
            } catch (\Exception $e) {
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function endCall(VoicePhoneCall $phoneCall)
    {
        $account = $phoneCall->getNumber()->getAccount();
        if (!$account instanceof PlivoVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        $client = $this->getClient($account);
        foreach ($phoneCall->getFlattenCallSids() as $requestId) {
            try {
                $client->calls->delete($requestId);
            } catch (PlivoResponseException $e) {
                $this->logger->info(sprintf(
                    'Unable to cancel the call, request_id = %s, message = %s',
                    $requestId, $e->getErrorMessage()
                ));
            } catch (\Exception $e) {
                $this->logger->info(sprintf(
                    'Unable to cancel the call, request_id = %s, code = %s, message = %s',
                    $requestId, $e->getCode(), $e->getMessage()
                ));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function kickParticipant(AbstractVoicePhoneCallParticipant $participant)
    {
        $phoneCall = $participant->getPhoneCall();
        $account   = $phoneCall->getNumber()->getAccount();

        if (!$account instanceof PlivoVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        try {
            $this->getClient($account)->calls->delete($participant->getCallSid());
        } catch (\Exception $e) {
        }
    }

    /**
     * {@inheritdoc}
     */
    public function muteParticipant(VoicePhoneCall $phoneCall, $callSid, $mute)
    {
        $account = $phoneCall->getNumber()->getAccount();
        if (!$account instanceof PlivoVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        $conference  = new ConferenceInterface($this->getClient($account)->client, $account->getAccountId());
        $participant = $phoneCall->getParticipantByCallSid($callSid);
        if (!$participant) {
            return;
        }

        if ($mute) {
            $conference->muteMember($phoneCall->getConferenceName(), [$participant->getMemberId()]);
        } else {
            $conference->unMuteMember($phoneCall->getConferenceName(), [$participant->getMemberId()]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function holdEndUser(VoicePhoneCall $phoneCall, $isHold)
    {
        $userParticipant = $phoneCall->getUserParticipants()->first();
        $account         = $phoneCall->getNumber()->getAccount();

        $holdUrl = $this->router->generate('plivo_user_put_on_hold_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $joinUrl = $this->router->generate('plivo_user_joins_conference_callback', [
            'account'     => $account->getId(),
            'accountAuth' => $account->getAccountAuth(),
            'callId'      => $phoneCall->getId(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        if ($isHold) {
            $this->transferParticipant($userParticipant, $holdUrl, 'POST');
        } else {
            $this->transferParticipant($userParticipant, $joinUrl, 'POST');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isCallActive(VoicePhoneCall $phoneCall)
    {
        $account = $phoneCall->getNumber()->getAccount();
        if (!$account instanceof PlivoVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        try {
            $call = $this->getClient($account)->calls->get($phoneCall->getCallSid());
            if ($call) {
                return !$call->endTime;
            }
        } catch (PlivoResponseException $e) {
            if ($e->getException(null) instanceof PlivoNotFoundException) {
                // it's possible that required call couldn't be found
                // just after creating, could be cache related or something
                // it means that the call is already created and still active
                // otherwise there will be the call object with non-empty endTime
                return true;
            }
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function transferParticipant(AbstractVoicePhoneCallParticipant $participant, $callbackUrl, $callbackMethod)
    {
        $account = $participant->getPhoneCall()->getNumber()->getAccount();
        if (!$account || !$account instanceof PlivoVoiceAccount) {
            throw new \RuntimeException('Voice number does not have an account reference.');
        }

        $start = microtime(true);

        try {
            return $this->getClient($account)->calls->transfer(
                $participant->getCallSid(),
                [
                    'legs'            => 'aleg',
                    'aleg_url'        => $callbackUrl,
                    'aleg_method'     => $callbackMethod,
                    'callback_url'    => $this->router->generate('plivo_async_callback', [], UrlGeneratorInterface::ABSOLUTE_URL),
                    'callback_method' => 'POST',
                ]
            );
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
        } finally {
            $this->logger->info(sprintf('[PlivoAdapter] Call transfer to %s took %.3fs', $callbackUrl, microtime(true) - $start));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prepareForColdTransfer(VoicePhoneCall $phoneCall)
    {
        foreach ($phoneCall->getAgentParticipants() as $participant) {
            $this->kickParticipant($participant);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRecording(VoicePhoneCall $phoneCall, $recordingSid)
    {
        // disabled for now
    }

    /**
     * @param PlivoVoiceAccount $account
     * @param string            $countryCode
     * @param string            $type
     * @param array             $options
     *
     * @return PlivoExistingNumber[]
     */
    public function getAvailablePhoneNumbers(PlivoVoiceAccount $account, $countryCode, $type, array $options)
    {
        $numbers = [];
        $options = array_merge($options, [
            'type' => $type,
        ]);

        try {
            $client  = $this->getClient($account);
            $result  = $client->getPhoneNumbers()->getList($countryCode, $options);
            $exclude = $this->getAccountNumbersList($account);

            /** @var PhoneNumber $apiNumber */
            foreach ($result as $apiNumber) {
                $numbers[] = new PlivoAvailableNumber(
                    $account,
                    $apiNumber,
                    isset($exclude['+'.$apiNumber->number])
                );
            }
        } catch (\Exception $e) {
        }

        return $numbers;
    }

    /**
     * @param PlivoVoiceAccount $account
     * @param int               $pageNum
     *
     * @return PlivoPaginate
     */
    public function getExistingPhoneNumbers(PlivoVoiceAccount $account, $pageNum = 1)
    {
        try {
            $exclude = $this->getAccountNumbersList($account);
            $limit   = 20;
            $offset  = ($pageNum - 1) * $limit;

            $result = $this->getClient($account)->getNumbers()->getList([
                'limit'  => $limit,
                'offset' => $offset,
            ]);

            $numbers = [];

            /** @var Number $apiNumber */
            foreach ($result as $apiNumber) {
                $numbers[] = new PlivoExistingNumber(
                    $account,
                    $apiNumber,
                    isset($exclude['+'.$apiNumber->number])
                );
            }

            return new PlivoPaginate($numbers, $pageNum, $result);
        } catch (\Exception $e) {
            return new PlivoPaginate([], $pageNum);
        }
    }

    /**
     * @param PlivoVoiceAccount $account
     * @param array             $data
     *
     * @return Number
     */
    public function buyNumber(PlivoVoiceAccount $account, array $data)
    {
        $number = $data['phoneNumber'];
        $number = ltrim($number, '+');

        $client = $this->getClient($account);
        $client->getPhoneNumbers()->buy($number);

        return $client->getNumbers()->get($number);
    }

    /**
     * @param PlivoVoiceAccount $account
     *
     * @return string[]
     */
    private function getAccountNumbersList(PlivoVoiceAccount $account)
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
     * @param PlivoVoiceAccount $account
     *
     * @return RestClient
     */
    private function getClient(PlivoVoiceAccount $account)
    {
        return new ProxyRestClient(
            $account->getAccountId(),
            $account->getAuthToken(),
            $this->settingsResolver->getPlivoProxyHost(),
            $this->settingsResolver->getPlivoProxyUsername(),
            $this->settingsResolver->getPlivoProxyPassword()
        );
    }
}
