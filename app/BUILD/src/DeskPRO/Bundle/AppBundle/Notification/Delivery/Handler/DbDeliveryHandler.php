<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Delivery\Handler;

use Application\DeskPRO\DBAL\Connection;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\Notification;
use Doctrine\ORM\EntityManager;

/**
 * Class DbDeliveryHandler.
 */
class DbDeliveryHandler extends AbstractDeliveryHandler
{
    const TYPE = 'notification.delivery.handler.db';

    const ACTION_ALERT_TABLE = 'notify_action_alerts';
    const NOTIFICATION_TABLE = 'notify_notifications';

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var array
     */
    private $messages = [];

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function schedule(MessageInterface $message)
    {
        $table = $this->getChannel($message);
        if (!isset($this->messages[$table])) {
            $this->messages[$table] = [];
        }

        $date = new \DateTime($message->getDate());

        $targetId = $message->getTarget();

        if ($message instanceof ActionAlert && $message->isBroadcast()) {
            $targetId = -100;
        }

        $this->messages[$table][] = [
            'uuid'         => $message->getId(),
            'target_id'    => $targetId,
            'date_created' => $date->format('Y-m-d H:i:s'),
            'data'         => json_encode($message->getData()['data']),
            'type'         => $message->getType(),
        ];
    }

    public function deliver()
    {
        /** @var Connection $connection */
        $connection = $this->em->getConnection();

        foreach ($this->messages as $table => $messages) {
            if (!empty($messages)) {
                $connection->batchInsert($table, $messages);
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
            return self::ACTION_ALERT_TABLE;
        } elseif ($message instanceof Notification) {
            return self::NOTIFICATION_TABLE;
        }

        throw new \InvalidArgumentException('Message should be ActionAlert or Notification');
    }
}
