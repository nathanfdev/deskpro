<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Languages;

use Symfony\Component\Finder\Finder;

/**
 * This is a simple fileystem reader that loads all phrases from all files under a directory.
 */
class AllPhrases
{
    /**
     * @var string
     */
    protected $dir;

    /**
     * @var string[]
     */
    protected $phrases = null;

    /**
     * @var string[]
     */
    protected $phrase_ids = null;

    /**
     * @var callback
     */
    protected $callback = null;

    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    /**
     * Set a callback function to be called for each phrase.
     *
     * The function will be passed $id and $phrase. You should accept the vars by reference and modify them directly.
     *
     * @param callback $callback
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
    }

    /**
     * @return string[]
     */
    public function getPhrases()
    {
        if ($this->phrases !== null) {
            return $this->phrases;
        }

        $this->phrases = [];

        $finder = Finder::create()->files()->name('*.php')->in([$this->dir]);

        foreach ($finder as $file) {
            /* @var $file \SplFileInfo */
            $path = $file->getRealPath();

            $phrase_group = include $path;
            if ($phrase_group && is_array($phrase_group)) {
                foreach ($phrase_group as $id => $phrase) {
                    if ($this->callback) {
                        $callback = $this->callback;
                        $callback($id, $phrase);
                    }

                    if ($id) {
                        $this->phrases[$id] = $phrase;
                    }
                }
            }
        }

        return $this->phrases;
    }

    /**
     * @return string[]
     */
    public function getPhraseIds()
    {
        if ($this->phrase_ids !== null) {
            return $this->phrase_ids;
        }

        $this->phrase_ids = array_keys($this->getPhrases());

        return $this->phrase_ids;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->getPhrases());
    }
}
