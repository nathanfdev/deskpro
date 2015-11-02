<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 *
 * @category Entities
 */
namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\TicketFlagged;

class TicketStar extends TicketFlagged
{
    private static $id_color_hex_map = array(
        self::STAR_BLUE   => '#0000FF',
        self::STAR_GREEN  => '#008000',
        self::STAR_ORANGE => '#FFA500',
        self::STAR_PINK   => '#FFC0CB',
        self::STAR_PURPLE => '#800080',
        self::STAR_RED    => '#FF0000',
        self::STAR_YELLOW => '#FFFF00',
    );

    /**
     * @param int $id
     *
     * @return string
     */
    public static function idToColorLabel($id)
    {
        return ucfirst(self::idToColorName($id));
    }

    /**
     * @param int $id
     *
     * @return string
     */
    public static function idToColorHex($id)
    {
        if (!isset(self::$id_color_hex_map[$id])) {
            throw new \InvalidArgumentException();
        }

        return self::$id_color_hex_map[$id];
    }

    /**
     * @return array
     */
    public static function getAll()
    {
        return self::$id_color_map;
    }
}
