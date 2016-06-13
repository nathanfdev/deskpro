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

namespace DeskPRO\Bundle\UpgradeBundle\Distro\Manifest;

class DistroRelease
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var string
     */
    private $track;

    /**
     * @var string
     */
    private $infoUrl;

    /**
     * DistroManifestRelease constructor.
     *
     * @param string $id
     * @param string $date
     * @param string $track
     * @param string $infoUrl
     */
    public function __construct($id, $date, $track, $infoUrl)
    {
        $this->id      = $id;
        $this->date    = \DateTime::createFromFormat('Y-m-d H:i:s', $date, new \DateTimeZone('UTC'));
        $this->track   = $track;
        $this->infoUrl = $infoUrl;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getTrack()
    {
        return $this->track;
    }

    /**
     * @return string
     */
    public function getInfoUrl()
    {
        return $this->infoUrl;
    }
}
