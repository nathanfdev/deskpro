<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler;

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;
use DeskPRO\Bundle\AppBundle\Util\HttpClient;
use DpSys\LowError\SystemErrorHandler;
use Firebase\JWT\JWT;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;

/**
 * Class DeskproDeliveryHandler.
 */
class DeskproDeliveryHandler extends MultiplexDeliverHandler
{
    const TYPE = 'notification.delivery.handler.deskpro';

    const MAX_MESSAGE_SIZE = 102400; // 100Kb for deskpro notification service by default;

    /**
     * @var array
     */
    private $messages = [];

    /** @var HttpClient */
    private $client;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var int
     */
    private $maxMessageSize;

    /**
     * @param SettingsResolver $resolver
     * @param HttpClient       $client
     */
    public function __construct(SettingsResolver $resolver, HttpClient $client)
    {
        $settingsBag    = $resolver->getGlobalSettings();
        $this->secret   = $settingsBag->get('notification.settings.deskpro_client.secret', '');
        $this->tries    = $settingsBag->get('notification.settings.deskpro_client.tries', 3);
        $maxMessageSize = $settingsBag->get('notification.settings.deskpro_client.max_message_size', static::MAX_MESSAGE_SIZE);

        if (!is_numeric($maxMessageSize) || $maxMessageSize <= 0) {
            $exception = new \InvalidArgumentException(
                'notification.settings.deskpro_client.max_message_size should have numeric format and should be greater than 0'
            );
            SystemErrorHandler::logException($exception, false, null, true);
        }
        $maxMessageSize       = is_numeric($maxMessageSize) && $maxMessageSize > 0 ? $maxMessageSize : static::MAX_MESSAGE_SIZE;
        $this->maxMessageSize = $maxMessageSize;
        $this->client         = $client;
    }

    /**
     * @param MessageInterface $message
     */
    public function schedule(MessageInterface $message)
    {
        //this is particular message should be sent only through db client
        if ($message->getType() === 'read.notifications.alert') {
            return;
        }

        $channel = 'private-'.$message->getTarget();
        if ($message instanceof ActionAlert && $message->isBroadcast()) {
            $channel = 'agent_public';
        }

        $data = [
                'target' => $message->getTarget(),
                'date'   => $message->getDate(),
                'id'     => $message->getId(),
                'type'   => $message->getType(),
            ] + $message->getData();

        $this->messages[] = [
            'channel' => $channel,
            'name'    => $this->getChannel($message),
            'data'    => $data,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function deliver()
    {
        if (!empty($this->messages)) {
            foreach (array_chunk($this->messages, 10) as $chunk) {
                $encodedDataLength = strlen(json_encode($chunk)); // we're interesting actual bytes, not chars

                try {
                    if ($encodedDataLength > $this->getMaxMessageSize()) {
                        $this->deliverDivided($chunk);
                    } else {
                        $this->innerDeliver($chunk);
                    }
                } catch (\Exception $e) {
                    SystemErrorHandler::logException($e);
                }
            }
        }
        $this->messages = [];
    }

    protected function encodeMultiplexData($data)
    {
        return $data;
    }

    /**
     * @param $chunk
     */
    protected function innerDeliver($chunk)
    {
        $tries     = $this->tries;
        $exception = null;
        do {
            $response = $this->triggerBatch($chunk);

            if ($response->getStatusCode() !== 200) {
                if (!$exception) {
                    $exception = new \RuntimeException('Failed to send Deskpro notifications service events: '.print_r($response, true));
                }
            } else {
                $exception = null;
            }
        } while ($response->getStatusCode() !== 200 && $tries-- > 0);

        if ($exception) {
            SystemErrorHandler::logException($exception);
        }
    }

    /**
     * @param $chunk
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function triggerBatch($chunk)
    {
        try {
            return $this->client->post('/send', [RequestOptions::JSON => ['jwt' => JWT::encode($chunk, $this->secret)]]);
        } catch (\Exception $e) {
            return new Response($e->getCode(), [], $e->getMessage());
        }
    }

    protected function getMaxMessageSize()
    {
        return $this->maxMessageSize;
    }
}
