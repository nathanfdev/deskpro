<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace Application\ImportBundle\Reader\Json;

use Application\ImportBundle\Reader\AbstractReader;
use Application\ImportBundle\Reader\NotFoundException;
use RecursiveIteratorIterator;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Symfony\Component\Finder\Iterator\SortableIterator;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Json data parser.
 *
 * Class JsonReader
 *
 * @property JsonConfig $config
 */
class JsonReader extends AbstractReader implements JsonReaderInterface
{
    /**
     * Constructor.
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
    public function checkConfig()
    {
        $paths = array(
            self::ENTITY_ARTICLE_PATH,
            self::ENTITY_ARTICLE_CATEGORY_PATH,
            self::ENTITY_DOWNLOAD_PATH,
            self::ENTITY_FEEDBACK_PATH,
            self::ENTITY_NEWS_PATH,
            self::ENTITY_PERSON_PATH,
            self::ENTITY_TICKET_PATH,
            self::ENTITY_ORGANIZATION_PATH,
        );

        if (!is_dir($this->config->getPath())) {
            throw new \RuntimeException(sprintf('`%s` is not a directory', $this->config->getPath()));
        }

        foreach ($paths as $path) {
            try {
                $this->getIterator($this->getEntityPath($path, 1));

                return true;
            } catch (NotFoundException $e) {
                // File not found, continue...
            }
        }

        throw new \RuntimeException(sprintf(
            'No json files found in directory `%s`. Checked in sub directories: %s.',
            $this->config->getPath(), implode(', ', array_map(
                function ($path) {
                    return '`'.$path.'`';
                },
                $paths
            ))
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getDirectoryFilesCount($entity_path, $batch_num)
    {
        $count    = 0;
        $iterator = $this->getIterator($this->getEntityPath($entity_path, $batch_num));

        foreach ($iterator as $file) {
            /* @var SplFileInfo $file */
            $content = @json_decode($file->getContents(), true);
            if (is_array($content)) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function getData($entity_path, $batch_num)
    {
        $data     = array();
        $iterator = $this->getIterator($this->getEntityPath($entity_path, $batch_num));

        foreach ($iterator as $file) {
            /* @var SplFileInfo $file */
            $content = @json_decode($file->getContents(), true);
            if (is_array($content)) {
                $data[] = $content;
            }
        }

        return $data;
    }

    /**
     * Returns entity type path.
     *
     * @param string $entity_type
     * @param int    $batch_num
     *
     * @return string
     */
    private function getEntityPath($entity_type, $batch_num)
    {
        return sprintf('%s/%d/%s', $this->config->getPath(), $batch_num, $entity_type);
    }

    /**
     * Returns directory json files iterator.
     *
     * @param string $path
     *
     * @throws NotFoundException
     *
     * @return RecursiveIteratorIterator
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
