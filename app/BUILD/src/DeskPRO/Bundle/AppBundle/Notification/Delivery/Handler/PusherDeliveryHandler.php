<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;
use DeskPRO\Bundle\AppBundle\Notification\NotificationService;
use DpSys\LowError\SystemErrorHandler;
use Pusher;

/**
 * Class PusherDeliveryHandler.
 */
class PusherDeliveryHandler extends MultiplexDeliverHandler
{
    const TYPE = 'notification.delivery.handler.pusher';

    /**
     * @var Pusher
     */
    private $pusher;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $channelPrefix = '';

    /**
     * @var array
     */
    private $messages = [];

    /**
     * @var bool
     */
    private $requestSoon = false;

    /**
     * @param Pusher           $pusher
     * @param SettingsResolver $resolver
     * @param Connection       $connection
     */
    public function __construct(
        Pusher $pusher,
        SettingsResolver $resolver,
        Connection $connection
    ) {
        $this->pusher        = $pusher;
        $this->channelPrefix = $resolver->getGlobalSettings()->get('notification.settings.pusher_client.channel_prefix', '');
        $this->tries         = $resolver->getGlobalSettings()->get('notification.settings.pusher_client.tries', 2);
        $this->connection    = $connection;
        \DpShutdown::add([$this, 'doDeliverSoon'], null, 'db_done_trans_commit');
        \DpShutdown::add([$this, 'doDeliverSoon']);
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

        $data = [
                'target' => $message->getTarget(),
                'date'   => $message->getDate(),
                'id'     => $message->getId(),
                'type'   => $message->getType(),
            ] + $message->getData();

        if ($message instanceof ActionAlert && $message->isBroadcast()) {
            $channelParts = [NotificationService::TARGET_BROADCAST];
        } else {
            $channelParts = ['private', $message->getTarget()];
        }

        if ($this->channelPrefix) {
            array_splice($channelParts, 1, 0, [$this->channelPrefix]);
        }

        $this->messages[] = [
            'channel' => implode('-', $channelParts),
            'name'    => $this->getChannel($message),
            'data'    => $data,
        ];
    }

    public function deliverSoon()
    {
        if ($this->connection->getTransactionNestingLevel() > 1) {
            $this->requestSoon = true;
        } else {
            $this->deliver();
        }
    }

    public function doDeliverSoon()
    {
        if (!$this->requestSoon) {
            return;
        }

        $this->requestSoon = false;

        if (empty($this->messages)) {
            return;
        }

        $this->deliver();
    }

    /**
     * {@inheritdoc}
     */
    public function deliver()
    {
        if (empty($this->messages)) {
            return;
        }

        foreach ($this->messages as &$message) {
            $message['data'] = json_encode($message['data']);
        }

        foreach (array_chunk($this->messages, 10) as $chunk) {
            $encodedDataLength = strlen(json_encode($chunk)); // we're interesting actual bytes, not chars

            try {
                if ($encodedDataLength > static::MAX_MESSAGE_SIZE) {
                    $this->deliverDivided($chunk);
                } else {
                    $this->innerDeliver($chunk);
                }
            } catch (\Exception $e) {
                SystemErrorHandler::logException($e);
            }
        }
        $this->messages = [];
    }

    /**
     * @param $chunk
     */
    protected function innerDeliver($chunk)
    {
        $tries     = $this->tries;
        $exception = null;
        do {
            $response = $this->pusher->triggerBatch($chunk, true, true);

            if ($response['status'] !== 200) {
                if (!$exception) {
                    $exception = new \RuntimeException('Failed to send Pusher events: '.print_r($response, true));
                }
            } else {
                $exception = null;
            }
        } while ($response['status'] !== 200 && $tries-- > 0);

        if ($exception) {
            SystemErrorHandler::logException($exception);
        }
    }

    /**
     * @param $chunk
     */
    protected function deliverDivided($chunk)
    {
        $channelGroupedMessages = [];
        foreach ($chunk as $message) {
            $messageChannel = $message['channel'];
            if (!isset($channelGroupedMessages[$messageChannel])) {
                $channelGroupedMessages[$messageChannel] = [];
            }
            $channelGroupedMessages[$messageChannel][] = $message;
        }
        foreach ($channelGroupedMessages as $channel => $channelMessages) {
            $encodedMessages     = json_encode($channelMessages);
            $channelMessagesSize = strlen($encodedMessages);

            if ($channelMessagesSize > $this->getMaxMessageSize()) {
                $this->deliverMultiplex($channel, $encodedMessages);
            } else {
                $this->innerDeliver($channelMessages);
            }
        }
    }
}
