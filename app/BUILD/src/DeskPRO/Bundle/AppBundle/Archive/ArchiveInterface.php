<?php

namespace DeskPRO\Bundle\AppBundle\Archive;

use RuntimeException;

interface ArchiveInterface
{
    public function open($filename);

    /**
     * @return Archive
     */
    public function getInfo();

    /**
     * Lists files and directories archive members.
     *
     * @throws RuntimeException In case archive could not be read
     *
     * @return array An array of File
     */
    public function getMembers();

    /**
     * Extracts specific members from the archive.
     *
     * @param string|array $members An array of members path
     *
     * @throws RuntimeException In case member could not be extracted
     *
     * @return ArchiveInterface
     */
    public function extractMembers($members);
}
