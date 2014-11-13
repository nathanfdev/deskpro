<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * @package Importer
 */

namespace Application\ImportBundle;

class DirectoryIteratorFilter extends \RecursiveFilterIterator
{
    /** @var bool  */
    private $exclude_done = false;

    public function excludeDone()
    {
        $this->exclude_done = true;
    }

    public function includeDone()
    {
        $this->exclude_done = false;
    }

    public function accept()
    {
        /** @var \SplFileInfo $current */
        $current = $this->current();

        // Invalid type
        if (!$current->isDir() && $current->getExtension() != 'json') {
            return false;
        }

        // Already done
        if ($this->exclude_done && file_exists($current->getPath() . '.done')) {
            return false;
        }

        return true;
    }
}
