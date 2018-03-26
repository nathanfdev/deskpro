<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets;

use Application\DeskPRO\Entity\TicketFlagged;
use JMS\Serializer\Annotation as JMS;

/**
 * Class TicketStar.
 */
class TicketStar
{
    /**
     * Star id.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $id;

    /**
     * Star name.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $name;

    /**
     * Star color.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $color;

    /**
     * Hex color representation.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    private $hex;

    /**
     * Constructor.
     *
     * @param int    $id
     * @param string $name
     */
    public function __construct($id, $name)
    {
        $this->id    = $id;
        $this->name  = ucfirst($name);
        $this->color = TicketFlagged::$colorMap[$id];
        $this->hex   = TicketFlagged::$hexMap[$id];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @return string
     */
    public function getHex()
    {
        return $this->hex;
    }
}
