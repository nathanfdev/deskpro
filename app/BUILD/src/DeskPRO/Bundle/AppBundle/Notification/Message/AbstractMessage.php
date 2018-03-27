<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Message;

use DeskPRO\Component\Util\RandUtils;

abstract class AbstractMessage implements MessageInterface
{
    /**
     * @var
     */
    protected $target;

    /**
     * @var
     */
    protected $data;

    /**
     * @var
     */
    protected $type;

    /**
     * @var string
     */
    protected $date;

    /**
     * @var string
     */
    protected $id;

    /**
     * @param $target
     * @param $data
     * @param $type
     */
    public function __construct($target, $data, $type)
    {
        $date         = new \DateTime();
        $this->target = $target;
        $this->data   = $data;
        $this->type   = (string) $type;
        $this->date   = $date->format(\DateTime::ISO8601);
        $this->id     = RandUtils::uuidV4();
    }

    /**
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'data' => $this->data,
        ];
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
}
