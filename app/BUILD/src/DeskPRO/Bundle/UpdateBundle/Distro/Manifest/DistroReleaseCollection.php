<?php

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
