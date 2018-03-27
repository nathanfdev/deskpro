<?php

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
