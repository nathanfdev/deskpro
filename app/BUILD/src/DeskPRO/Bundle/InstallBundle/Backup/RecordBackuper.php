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

namespace DeskPRO\Bundle\InstallBundle\Backup;

use DeskPRO\Component\Util\RandUtils;
use Symfony\Component\Filesystem\Filesystem;

class RecordBackuper
{
    /**
     * @var string
     */
    private $backupDir;

    private $fs;

    /**
     * RecordBackuper constructor.
     *
     * @param string $backupDir
     */
    public function __construct($backupDir)
    {
        $this->backupDir = rtrim($backupDir, '/\\');
        $this->fs        = new Filesystem();
    }

    /**
     * @param string       $type The type of record (acts as a namespace)
     * @param string       $name The name or ID of the recorcd (used in the filename; useful as a hint in the fs)
     * @param string|array $data The actual data to save. Either a string, or any thing else will be json_encoded
     * @param array        $meta Any metadata to store along with the data (e.g real db IDs or dates etc)
     */
    public function backupRecord($type, $name, $data, array $meta = [])
    {
        $meta['@type']      = $type;
        $meta['@name']      = $name;
        $meta['@createdAt'] = date('Y-m-d H:i:s');

        if (!is_scalar($data)) {
            $data               = json_encoe($data, \JSON_PRETTY_PRINT);
            $meta['@isEncoded'] = true;
        }

        $name = date('YmdHis').'-'.RandUtils::randomStringFormat('%3A').'-'.$name;

        $targetDir = $this->backupDir.DIRECTORY_SEPARATOR.$this->safeName($type);
        $this->fs->mkdir($targetDir, 0777);
        $this->fs->dumpFile($targetDir.DIRECTORY_SEPARATOR.$this->safeName($name), $data);
        $this->fs->dumpFile($targetDir.DIRECTORY_SEPARATOR.$this->safeName($name).'.meta', json_encode($meta, \JSON_PRETTY_PRINT));
    }

    /**
     * @param string $name
     *
     * @return string mixed
     */
    private function safeName($name)
    {
        $name = preg_replace('#[^a-zA-Z0-9\-_\.]#', '_', $name);
        $name = preg_replace('#_{2,}#', '_', $name);

        // max 80 chars
        if (isset($name[81])) {
            $name = substr($name, 0, 80);
        }

        return $name;
    }
}
