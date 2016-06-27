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

namespace DeskPRO\Bundle\DevBundle\Language;

use Symfony\Component\Finder\Finder;

class PhraseProject
{
    /**
     * @var \SplFileInfo[]
     */
    private $files;

    /**
     * @param string          $langDir
     * @param string|string[] $projectId
     *
     * @return PhraseProject
     */
    public static function createProject($langDir, $projectId)
    {
        $projectIds = (array) $projectId;

        $dirs = [];

        foreach ($projectIds as $projectId) {
            switch ($projectId) {
                case 'portal':
                case 'user': // user is old alias for portal
                    $dirs = array_merge($dirs, [
                        $langDir.'/portal',
                        $langDir.'/user',
                    ]);
                    break;

                case 'agent':
                    $dirs = array_merge($dirs, [
                        $langDir.'/agent',
                    ]);
                    break;

                case 'other':
                    $dirs = array_merge($dirs, [
                        $langDir.'/adm',
                        $langDir.'/admin',
                        $langDir.'/api',
                    ]);
                    break;

                default:
                    throw new \InvalidArgumentException();
            }
        }

        $dirs = array_filter($dirs, function ($v) { return is_dir($v); });

        if (!$dirs) {
            return new self([]);
        }

        $files = Finder::create()
            ->in($dirs)
            ->files()
            ->name('*.php');

        return new self(iterator_to_array($files));
    }

    /**
     * PhraseProject constructor.
     *
     * @param \SplFileInfo[] $files
     */
    public function __construct(array $files)
    {
        $this->files = $files;
    }

    /**
     * @return \SplFileInfo[]
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @return \SplFileInfo[]
     */
    public function getFilesMap()
    {
        $map = [];

        foreach ($this->getFiles() as $f) {
            $map[self::getPathFileId($f)] = $f;
        }

        return $map;
    }

    /**
     * @param string $fileId
     *
     * @return null|\SplFileInfo
     */
    public function getFileByFileId($fileId)
    {
        foreach ($this->getFiles() as $f) {
            if ($fileId === self::getPathFileId($f)) {
                return $f;
            }
        }

        return;
    }

    /**
     * Returns an 'id' for a file path. E.g., 'portal/foo.php' instead of a full path.
     *
     * @param \SplFileInfo $file
     *
     * @return string
     */
    public static function getPathFileId(\SplFileInfo $file)
    {
        return basename($file->getPath()).'/'.$file->getFilename();
    }
}
