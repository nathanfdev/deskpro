<?php

namespace Application\EmailBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class Mail extends Event
{
    const PROCESSED  = 'processed';
    const DROPPED    = 'dropped';
    const DELIVERED  = 'delivered';
    const DEFERRED   = 'deferred';
    const BOUNCE     = 'bounce';
    const OPEN       = 'open';
    const CLICK      = 'click';
    const SPAMREPORT = 'spamreport';

    protected $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * @param $key
     * @param null $default
     */
    public function get($key, $default = null)
    {
        return $this->has($key) ? $this->data[$key] : $default;
    }

    /**
     * @param $key
     *
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->data;
    }
}
