<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler;

use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\Notification;
use DeskPRO\Bundle\AppBundle\Util\HttpClient;
use DpSys\LowError\SystemErrorHandler;
use Firebase\JWT\JWT;
use GuzzleHttp\RequestOptions;

/**
 * Class DeskproDeliveryHandler.
 */
class DeskproDeliveryHandler extends AbstractDeliveryHandler
{
    const TYPE = 'notification.delivery.handler.deskpro';

    const CHANNEL_ACTION_ALERT = 'action_alert';
    const CHANNEL_USER_NOTIFY  = 'user_notify';

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
     * @param SettingsResolver $resolver
     * @param HttpClient       $client
     */
    public function __construct(SettingsResolver $resolver, HttpClient $client)
    {
        $settingsBag  = $resolver->getGlobalSettings();
        $this->secret = $settingsBag->get('notification.settings.deskpro_client.secret', '');

        $this->client = $client;
    }

    /**
     * @param MessageInterface $message
     */
    public function schedule(MessageInterface $message)
    {

        //this is particular message should be sent only throught db client
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

    public function deliver()
    {
        if (!empty($this->messages)) {
            foreach (array_chunk($this->messages, 10) as $chunk) {
                $tries     = 3;
                $exception = null;
                do {
                    $response = $this->triggerBatch($chunk);
                    if ($response->getStatusCode() !== 200) {
                        if (!$exception) {
                            $exception = new \RuntimeException('Failed to send events: '.print_r($response, true));
                        }
                    } else {
                        $exception = null;
                    }
                } while ($response->getStatusCode() !== 200 && $tries-- > 0);

                if ($exception) {
                    SystemErrorHandler::logException($exception);
                }
            }
        }
        $this->messages = [];
    }

    /**
     * @param MessageInterface $message
     *
     * @return string
     */
    protected function getChannel(MessageInterface $message)
    {
        if ($message instanceof ActionAlert) {
            return self::CHANNEL_ACTION_ALERT;
        } elseif ($message instanceof Notification) {
            return self::CHANNEL_USER_NOTIFY;
        }

        throw new \InvalidArgumentException('Message should be ActionAlert or Notification');
    }

    /**
     * @param $chunk
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function triggerBatch($chunk)
    {
        return $this->client->post('/send', [RequestOptions::JSON => ['jwt' => JWT::encode($chunk, $this->secret)]]);
    }
}
