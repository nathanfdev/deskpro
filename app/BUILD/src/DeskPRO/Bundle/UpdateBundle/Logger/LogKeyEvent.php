<?php

namespace DeskPRO\Bundle\UpdateBundle\Logger;

class LogKeyEvent
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var array
     */
    private $data;

    /**
     * LogKeyEvent constructor.
     *
     * @param string|string[] $id
     * @param array           $data
     */
    private function __construct($id, array $data = [])
    {
        $this->id   = is_array($id) ? implode('.', $id) : $id;
        $this->data = $data;
    }

    /**
     * @param string     $id
     * @param \Exception $e
     * @param array      $data
     *
     * @return LogKeyEvent
     */
    public static function createForException($id, \Exception $e, array $data = [])
    {
        $data['exception'] = $e;

        return new self($id, $data);
    }

    /**
     * @param string $id
     * @param array  $data
     *
     * @return LogKeyEvent
     */
    public static function create($id, array $data = [])
    {
        return new self($id, $data);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->data);
    }
}
