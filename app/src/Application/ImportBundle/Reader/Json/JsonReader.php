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

namespace Application\ImportBundle\Reader\Json;

use Application\ImportBundle\Reader\AbstractReader;
use RecursiveIteratorIterator;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Symfony\Component\Finder\Iterator\SortableIterator;
use Symfony\Component\Finder\SplFileInfo;
use Exception;

/**
 * Json data parser
 *
 * Class JsonReader
 * @package Application\ImportBundle\Reader\Json
 */
class JsonReader extends AbstractReader implements JsonReaderInterface
{
    /**
     * Constructor
     *
     * @param JsonConfig $config
     */
    public function __construct(JsonConfig $config)
    {
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function getDirectoryFilesCount(JsonConfig $config)
    {
        $count    = 0;
        $iterator = $this->getIterator($config->getPath());

        foreach ($iterator as $file) {
            /** @var SplFileInfo $file */
            $content = @json_decode($file->getContents(), true);
            if (is_array($content)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(JsonConfig $config)
    {
        $data     = array();
        $iterator = $this->getIterator($config->getPath());

        foreach ($iterator as $file) {
            /** @var SplFileInfo $file */
            $content = @json_decode($file->getContents(), true);
            if (is_array($content)) {
                $data[] = $content;
            }
        }

        return $data;
    }

    /**
     * Returns directory json files iterator
     *
     * @param string $path
     *
     * @return RecursiveIteratorIterator
     * @throws Exception
     */
    private function getIterator($path)
    {
        if (is_dir($path) === false) {
            throw new NotFoundException(sprintf('Path `%s` not found', $path));
        }

        return new SortableIterator(
            new RecursiveIteratorIterator(
                new DirectoryIteratorFilter(
                    new RecursiveDirectoryIterator(
                        $path,
                        RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::CURRENT_AS_FILEINFO
                    )
                ),
                RecursiveIteratorIterator::SELF_FIRST | RecursiveIteratorIterator::LEAVES_ONLY
            ),
            SortableIterator::SORT_BY_NAME
        );
    }
}
