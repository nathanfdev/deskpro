<?php

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
