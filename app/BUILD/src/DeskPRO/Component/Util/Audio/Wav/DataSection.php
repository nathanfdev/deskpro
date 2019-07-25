<?php

namespace DeskPRO\Component\Util\Audio\Wav;

class DataSection
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var int[]
     */
    protected $raw;

    public function __construct($id, $size, $raw)
    {
        $this->id   = $id;
        $this->size = $size;
        $this->raw  = $raw;
    }

    /**
     * @return int[]
     */
    public function getRaw()
    {
        return $this->raw;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }
}
