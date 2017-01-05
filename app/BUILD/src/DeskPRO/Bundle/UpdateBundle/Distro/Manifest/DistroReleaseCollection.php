<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
     * Filter this collection by a callback, and return a new collection.
     *
     * @param callable $cb
     *
     * @return DistroReleaseCollection
     */
    public function filterBy($cb)
    {
        $releases = ListUtils::filter($this->releases, $cb);

        return new self($releases);
    }

    /**
     * @param array $criteria
     *
     * @return DistroReleaseCollection
     */
    public function filterByCriteria(array $criteria)
    {
        return $this->filterBy(function (DistroRelease $r) use ($criteria) {
            foreach ($criteria as $type => $opt) {
                switch ($type) {
                    case 'with_flags':
                        $opt = (array) $opt;
                        foreach ($opt as $f) {
                            if (!$r->hasFlag($f)) {
                                return false;
                            }
                        }
                        break;
                }
            }

            return true;
        });
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

        return ListUtils::first($this->releases, function (DistroRelease $r) use ($id) {
            return $r->getId() === $id;
        });
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
