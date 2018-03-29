<?php

namespace DeskPRO\Component\TaskRunner\Task;

class Task implements TaskInterface
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
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->id   = date('YmdHis').'-'.uniqid('', true);
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $k
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($k, $default = null)
    {
        return isset($this->data[$k]) ? $this->data[$k] : $default;
    }

    /**
     * @param string $k
     *
     * @return bool
     */
    public function has($k)
    {
        return array_key_exists($k, $this->data);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '<Task::'.$this->id.'>';
    }
}
