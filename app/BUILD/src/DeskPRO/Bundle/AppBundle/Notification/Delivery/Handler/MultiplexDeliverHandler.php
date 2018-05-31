<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler;

/**
 * Class MultiplexDeliveryHandler.
 */
abstract class MultiplexDeliverHandler extends AbstractDeliveryHandler
{
    const TYPE = 'notification.delivery.handler.multiplex_abstract';

    /**
     * @param array $chunk
     */
    abstract protected function innerDeliver($chunk);

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

    /**
     * @param $channel
     * @param $encodedMessages
     */
    protected function deliverMultiplex($channel, $encodedMessages)
    {
        // 1Kb of overhead is more than anyone will ever need :)
        $encodedMessagesParts = str_split(base64_encode($encodedMessages), intval(0.9 * $this->getMaxMessageSize()));
        $i                    = 0;
        $allParts             = count($encodedMessagesParts);
        $multiplexId          = time().'-'.hash('crc32b', $encodedMessages); // crc32b just much faster than md5 or sha1
        foreach (array_chunk($encodedMessagesParts, 10) as $encodedMessagesPartsChunk) {
            $chunk = [];
            foreach ($encodedMessagesPartsChunk as $part) {
                $chunk[] = [
                    'channel' => $channel,
                    'name'    => static::CHANNEL_ACTION_ALERT,
                    'data'    => $this->encodeMultiplexData([
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

    /**
     * @param $data
     *
     * @return string
     */
    protected function encodeMultiplexData($data)
    {
        return json_encode($data);
    }

    /**
     * @return int
     */
    protected function getMaxMessageSize()
    {
        return static::MAX_MESSAGE_SIZE;
    }
}
