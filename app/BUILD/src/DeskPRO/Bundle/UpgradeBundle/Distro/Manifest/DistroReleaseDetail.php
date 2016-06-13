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

class DistroReleaseDetail
{
    private $release;

    /**
     * @var string
     */
    private $zipUrl;

    /**
     * @var string
     */
    private $sha256;

    /**
     * DistroReleaseDetail constructor.
     *
     * @param DistroRelease $release
     * @param string        $zipUrl
     * @param string        $sha256
     */
    public function __construct(DistroRelease $release, $zipUrl, $sha256)
    {
        $this->release = $release;
        $this->zipUrl  = $zipUrl;
        $this->sha256  = $sha256;
    }

    /**
     * @return DistroRelease
     */
    public function getRelease()
    {
        return $this->release;
    }

    /**
     * @return string
     */
    public function getZipUrl()
    {
        return $this->zipUrl;
    }

    /**
     * @return string
     */
    public function getSha256()
    {
        return $this->sha256;
    }
}
