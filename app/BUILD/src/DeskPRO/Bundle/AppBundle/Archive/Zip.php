<?php

namespace DeskPRO\Bundle\AppBundle\Archive;

use DeskPRO\Bundle\AppBundle\AppEnv\AppEnvInterface;
use Orb\Util\Numbers;
use Symfony\Component\Filesystem\Filesystem;

class Zip implements ArchiveInterface
{
    private $zip;

    /**
     * @var AppEnvInterface
     */
    private $environment;

    private $tmpFolder = false;

    public function __construct(AppEnvInterface $environment)
    {
        $this->zip         = new \ZipArchive();
        $this->environment = $environment;
    }

    public function __destruct()
    {
        if ($this->tmpFolder) {
            $fs = new Filesystem();
            $fs->remove($this->tmpFolder);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function open($filename)
    {
        if (!$this->zip->open($filename)) {
            throw new \Exception('Could not open archive '.$filename);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMembers()
    {
        $content = [];
        for ($i = 0; $i < $this->zip->numFiles; ++$i) {
            $file = $this->zip->statIndex($i);
            if ($file['crc'] < 0) {
                $file['crc'] = sprintf('%u', $file['crc']);
            }

            if ($file['size']) {
                $file['filesize_readable'] = Numbers::filesizeDisplay($file['size']);
            }
            $file['comment'] = $this->zip->getCommentIndex($i);
            $opsys           = null;
            $attr            = null;
            if ($this->zip->getExternalAttributesIndex($i, $opsys, $attr)) {
                $file['opsys'] = $opsys;
                $file['attr']  = $attr;
            }
            $content[] = $file;
        }

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function extractMembers($members)
    {
        $tmpDir          = $this->environment->getUserTmpDir();
        $fileId          = uniqid('archive', true);
        $this->tmpFolder = $tmpDir.DIRECTORY_SEPARATOR.'archive'.DIRECTORY_SEPARATOR.$fileId.DIRECTORY_SEPARATOR;
        $files           = [];
        if ($this->zip->extractTo($this->tmpFolder, $members)) {
            if (!is_array($members)) {
                $members = [$members];
            }
            foreach ($members as $member) {
                $files[$member] = $this->tmpFolder.$member;
            }
        } else {
            throw new \Exception('Error while getting the files');
        }

        return $files;
    }

    /**
     * {@inheritdoc}
     */
    public function getInfo()
    {
        $archive = new Archive();
        $archive->setType('zip');
        $archive->setComment($this->zip->getArchiveComment());
        $archive->setFilesCount($this->zip->numFiles);
        $archive->setStatus($this->zip->getStatusString());

        return $archive;
    }
}
