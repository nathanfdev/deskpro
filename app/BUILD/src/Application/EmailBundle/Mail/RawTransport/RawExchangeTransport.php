<?php

namespace Application\EmailBundle\Mail\RawTransport;

use Application\DeskPRO\Email\EmailAccount\AccountConfigInterface;
use Application\DeskPRO\Email\EmailAccount\OutgoingAccount\ExchangeConfig;
use Application\DeskPRO\Email\EmailAccount\OutgoingAccount\Office365ExchangeConfig;
use Application\DeskPRO\EmailGateway\Fetcher\Office365;
use Application\EmailBundle\Mail\RawMessage\RawMessageDecoderInterface;
use Psr\Log\LoggerInterface;

class RawExchangeTransport implements RawTransportInterface
{
    /**
     * @var AccountConfigInterface
     */
    protected $config;

    /**
     * @var RawMessageDecoderInterface
     */
    protected $decoder;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \ExchangeWebServices
     */
    protected $ews;

    public function __construct(AccountConfigInterface $config, RawMessageDecoderInterface $decoder, LoggerInterface $logger)
    {
        if (!($config instanceof ExchangeConfig) && !($config instanceof Office365ExchangeConfig)) {
            throw new \InvalidArgumentException('This transport supports only ExchangeConfig or Office365ExchangeConfig configs');
        }
        $this->config  = $config;
        $this->decoder = $decoder;
        $this->logger  = $logger;
    }

    /**
     * @return \ExchangeWebServices
     */
    protected function ews()
    {
        if ($this->ews === null) {
            $token = null;

            if ($this->config instanceof Office365ExchangeConfig && $this->config->getRefreshToken()) {
                $oauthClient = Office365::createOauthClient($this->config->getClientId(), $this->config->getClientSecret());
                $accessToken = $oauthClient->getAccessToken('refresh_token', [
                    'refresh_token' => $this->config->getRefreshToken(),
                ]);

                $token = $accessToken->getToken();
            }

            $this->ews = new \ExchangeWebServices($this->config->host, $this->config->user, $this->config->password, $token);
        }

        return $this->ews;
    }

    public function sendRawMessage($from, array $tos, $raw_fp, array &$failed = null)
    {
        $sent = 0;

        if (!$tos) {
            return $sent;
        }

        $msg = new \EWSType_MessageType();

        $msg->MimeContent    = new \EWSType_MimeContentType();
        $msg->MimeContent->_ = base64_encode(stream_get_contents($raw_fp, -1, 0));

        $msgRequest                     = new \EWSType_CreateItemType();
        $msgRequest->Items              = new \EWSType_NonEmptyArrayOfAllItemsType();
        $msgRequest->Items->Message     = $msg;
        $msgRequest->MessageDisposition = 'SendOnly';

        $this->logger->info('[RawExchangeTransport] Sending raw mail');

        try {
            $response = $this->ews()->CreateItem($msgRequest);
            $okay     = false;

            if ($response && $response->ResponseMessages && ($response = $response->ResponseMessages->CreateItemResponseMessage)) {
                if ('Error' === $response->ResponseClass) {
                    $this->logger->error(sprintf('[RawExchangeTransport] %s', $response->MessageText));
                    $failed = $tos;
                } elseif ('Success' === $response->ResponseClass) {
                    $this->logger->info('[RawExchangeTransport] success');
                    ++$sent;
                    $okay = true;
                }
            }

            if (!$okay) {
                $this->logger->info('[RawExchangeTransport] did not send');
                $this->logger->debug('last response: '.$this->ews()->getClient()->__getLastResponse());
            }
        } catch (\Exception $e) {
            $this->logger->info(sprintf('[RawExchangeTransport] Exception: <%s> [%s] %s', get_class($e), $e->getCode(), $e->getMessage()));
            $this->logger->debug($this->ews()->getClient()->__getLastResponse());
            throw $e;
        }

        return $sent;
    }
}
