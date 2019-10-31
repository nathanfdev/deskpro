<?php

namespace DeskPRO\Component\Util\Audio\Wav;

class Header
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $format;

    /**
     * Header constructor.
     *
     * @param $id
     * @param $size
     * @param $format
     */
    public function __construct($id, $size, $format)
    {
        $this->id     = $id;
        $this->size   = $size;
        $this->format = $format;
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

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }
}
