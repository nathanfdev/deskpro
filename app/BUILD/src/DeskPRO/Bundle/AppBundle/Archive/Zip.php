<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Archive;

use DeskPRO\Bundle\AppBundle\AppEnv\AppEnvInterface;
use Orb\Util\Numbers;
use Orb\Util\Util;

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
            Util::delTree($this->tmpFolder);
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
