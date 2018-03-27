<?php

namespace DeskPRO\Bundle\UpdateBundle\DbBackup;

interface DbBackupInterface
{
    /**
     * @param string $targetPath
     * @param array  $dbInfo
     * @param array  $options
     *
     * @throws DbBackupException
     */
    public function backupDatabase($targetPath, array $dbInfo, array $options = []);
}
