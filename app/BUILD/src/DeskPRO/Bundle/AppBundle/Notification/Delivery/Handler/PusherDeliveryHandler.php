<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\Notification;
use DeskPRO\Bundle\AppBundle\Notification\NotificationService;
use DpSys\LowError\SystemErrorHandler;
use Pusher;

/**
 * Class PusherDeliveryHandler.
 */
class PusherDeliveryHandler extends AbstractDeliveryHandler
{
    const TYPE = 'notification.delivery.handler.pusher';

    const CHANNEL_ACTION_ALERT = 'action_alert';
    const CHANNEL_USER_NOTIFY  = 'user_notify';

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
     * @var int
     */
    private $tries;

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

        //this is particular message should be sent only throught db client
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

            if ($channelMessagesSize > static::MAX_MESSAGE_SIZE) {
                $this->deliverMultiplex($channel, $encodedMessages);
            } else {
                $this->innerDeliver($channelMessages);
            }
        }
    }

    /**
     * @param $channel
     * @param $encodedMessages
     */
    protected function deliverMultiplex($channel, $encodedMessages)
    {
        // 1Kb of overhead is more than anyone will ever need :)
        $encodedMessagesParts = str_split(base64_encode($encodedMessages), intval(0.9 * static::MAX_MESSAGE_SIZE));
        $i                    = 0;
        $allParts             = count($encodedMessagesParts);
        $multiplexId          = time().'-'.hash('crc32b', $encodedMessages); // crc32b just much faster than md5 or sha1
        foreach (array_chunk($encodedMessagesParts, 10) as $encodedMessagesPartsChunk) {
            $chunk = [];
            foreach ($encodedMessagesPartsChunk as $part) {
                $chunk[] = [
                    'channel' => $channel,
                    'name'    => self::CHANNEL_ACTION_ALERT,
                    'data'    => json_encode([
                        'type'        => 'multiplex_message',
                        'part'        => ++$i,
                        'parts'       => $allParts,
                        'data'        => $part,
                        'multiplexId' => $multiplexId,
                    ]),
                ];
            }
            $this->innerDeliver($chunk);
        }
    }
}
