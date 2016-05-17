<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
