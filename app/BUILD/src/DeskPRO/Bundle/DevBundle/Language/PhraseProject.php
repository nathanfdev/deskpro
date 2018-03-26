<?php

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

        foreach ($projectIds as $projectId) {
            switch ($projectId) {
                case 'portal':
                case 'user': // user is old alias for portal
                    $files = Finder::create()->in($langDir)->files()->name('portal.php');
                    break;

                case 'agent':
                    $files = Finder::create()->in($langDir)->files()->name('agent.php');
                    break;

                case 'other':
                    $files = Finder::create()->in($langDir)->files()->name('/(admin|api|general)\.php/');
                    break;

                default:
                    throw new \InvalidArgumentException();
            }
        }

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
     * @param \SplFileInfo $file
     *
     * @return array
     */
    public function groupPhrasesFromFile(\SplFileInfo $file)
    {
        $grouped = [];

        $phrases = require $file->getRealPath();
        foreach ($phrases as $phraseId => $phrase) {
            $parts = explode('.', $phraseId);
            if (!isset($grouped[$parts[1]])) {
                $grouped[$parts[1]] = [];
            }

            $grouped[$parts[1]][$phraseId] = $phrase;
        }

        return $grouped;
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
    private static function getPathFileId(\SplFileInfo $file)
    {
        return $file->getFilename();
    }
}
