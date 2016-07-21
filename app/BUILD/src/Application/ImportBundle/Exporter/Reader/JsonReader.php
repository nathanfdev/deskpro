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

namespace Application\ImportBundle\Exporter\Reader;

use Orb\Util\Strings;
use RecursiveIteratorIterator;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Symfony\Component\Finder\Iterator\SortableIterator;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Json data parser.
 *
 * Class JsonReader
 */
class JsonReader
{
    /**
     * Returns count of json files in the dir
     * One record per file.
     *
     * @param string $path
     * @param string $modelClass
     * @param int    $batchNum
     *
     * @return int
     */
    public function getCount($path, $modelClass, $batchNum)
    {
        $count    = 0;
        $iterator = $this->getIterator($this->getEntityPath($path, $modelClass, $batchNum));
        while ($iterator->getIterator()->next()) {
            ++$count;
        }

        return $count;
    }

    /**
     * Returns directory files data
     * Reads all directory json files, decode and returns  array.
     *
     * @param string $path
     * @param string $modelClass
     * @param int    $batchNum
     *
     * @return array
     */
    public function getData($path, $modelClass, $batchNum)
    {
        $data     = [];
        $iterator = $this->getIterator($this->getEntityPath($path, $modelClass, $batchNum));

        foreach ($iterator as $file) {
            $data[$file->getBasename('.'.$file->getExtension())] = $file->getContents();
        }

        return $data;
    }

    /**
     * Returns entity type path.
     *
     * @param string $path
     * @param string $modelClass
     * @param int    $batchNum
     *
     * @return string
     */
    private function getEntityPath($path, $modelClass, $batchNum)
    {
        $modelClass = (new \ReflectionClass($modelClass))->getShortName();
        $entityPath = Strings::camelCaseToUnderscore($modelClass);

        return sprintf('%s/%d/%s', $path, $batchNum, $entityPath);
    }

    /**
     * Returns directory json files iterator.
     *
     * @param string $path
     *
     * @throws NotFoundException
     *
     * @return SortableIterator|SplFileInfo[]
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
