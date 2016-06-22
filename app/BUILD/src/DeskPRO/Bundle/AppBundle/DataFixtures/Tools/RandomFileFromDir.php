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

namespace DeskPRO\Bundle\AppBundle\DataFixtures\Tools;

/**
 * Class RandomFileFromDir.
 */
class RandomFileFromDir implements \Countable
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var \SplFileInfo[]
     */
    private $files = [];

    /**
     * @var int
     */
    private $count;

    /**
     * RandomFileFromDir constructor.
     *
     * @param string $path
     * @param bool   $deep Read all sub-dirs as well
     */
    public function __construct($path, $deep = true)
    {
        $this->path = $path;

        $flags = \FilesystemIterator::CURRENT_AS_FILEINFO
            | \FilesystemIterator::SKIP_DOTS
            | \FilesystemIterator::UNIX_PATHS;

        if ($deep) {
            $iter = new \RecursiveDirectoryIterator($this->path, $flags);
        } else {
            $iter = new \FilesystemIterator($this->path, $flags);
        }

        $this->files = iterator_to_array($iter, false);
        $this->count = count($this->files);
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->count;
    }

    /**
     * @return \SplFileInfo
     */
    public function next()
    {
        $rand = mt_rand(0, $this->count - 1);

        return $this->files[$rand];
    }
}
