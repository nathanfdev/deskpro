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

namespace DeskPRO\Bundle\UpdateBundle\Distro\Manifest;

use DeskPRO\Component\Util\ListUtils;

class DistroReleaseCollection implements \Countable
{
    /**
     * @var DistroRelease[]
     */
    private $releases;

    /**
     * @var int|null
     */
    private $count;

    /**
     * DistroReleaseCollection constructor.
     *
     * @param DistroRelease[] $releases
     */
    public function __construct(array $releases)
    {
        $this->releases = array_values($releases);
    }

    /**
     * @param string $id
     *
     * @return DistroRelease|null
     */
    public function getById($id)
    {
        if (empty($this->releases)) {
            return;
        }

        return ListUtils::first($this->releases, function (DistroRelease $r) use ($id) { return $r->getId() === $id; });
    }

    /**
     * @return DistroRelease|null
     */
    public function getLatest()
    {
        if (empty($this->releases)) {
            return;
        }

        return ListUtils::last($this->releases);
    }

    /**
     * @return DistroRelease[]
     */
    public function getReleases()
    {
        return $this->releases;
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        if ($this->count === null) {
            $this->count = count($this->releases);
        }

        return $this->count;
    }
}
