<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Message;

/**
 * Class ActionAlert.
 */
class ActionAlert extends AbstractMessage
{
    /**
     * @var array
     */
    private $metaData;

    /**
     * {@inheritdoc}
     */
    public function __construct($target, $data, $type, array $metaData = [])
    {
        parent::__construct($target, $data, $type);
        $this->metaData = $metaData;
    }

    /**
     * @return mixed
     */
    public function getMetaData()
    {
        return $this->metaData;
    }

    public function setBroadcast($broadcast = true)
    {
        $this->metaData['broadcast'] = $broadcast;
    }

    public function isBroadcast()
    {
        return isset($this->metaData['broadcast']) && $this->metaData['broadcast'];
    }
}
