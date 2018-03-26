<?php

namespace DeskPRO\Bundle\ImportBundle\Storage\StorageAdapter;

/**
 * Directory json files filter.
 *
 * Class DirectoryIteratorFilter
 */
class DirectoryIteratorFilter extends \RecursiveFilterIterator
{
    /**
     * Constructor.
     *
     * @param \RecursiveIterator $iterator
     */
    public function __construct(\RecursiveIterator $iterator)
    {
        parent::__construct($iterator);
    }

    /**
     * {@inheritdoc}
     */
    public function accept()
    {
        /** @var \SplFileInfo $current */
        $current = $this->current();

        // Invalid type
        if ($current->isDir() === false && $current->getExtension() !== 'json') {
            return false;
        }

        return true;
    }
}
