<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\EmailBundle\Mail\RawTransport;

use Application\DeskPRO\Email\EmailAccount\OutgoingAccount\ExchangeConfig;
use Application\EmailBundle\Mail\RawMessage\RawMessageDecoderInterface;
use Psr\Log\LoggerInterface;

class RawExchangeTransport implements RawTransportInterface
{
    /**
     * @var ExchangeConfig
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

    public function __construct(ExchangeConfig $config, RawMessageDecoderInterface $decoder, LoggerInterface $logger)
    {
        $this->config  = $config;
        $this->decoder = $decoder;
        $this->logger  = $logger;
    }

    /**
     * @return \ExchangeWebServices
     */
    protected function ews()
    {
        return $this->ews
                ? $this->ews
                : $this->ews = new \ExchangeWebServices($this->config->host, $this->config->user, $this->config->password);
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
