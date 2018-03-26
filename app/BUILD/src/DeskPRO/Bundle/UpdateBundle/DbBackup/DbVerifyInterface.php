<?php

namespace DeskPRO\Bundle\UpdateBundle\DbBackup;

interface DbVerifyInterface
{
    /**
     * Try to verify that the complete dump is there.
     *
     * @param string $filename
     *
     * @throws DbBackupException
     *
     * @return true This method always returns true, else an exception is thrown
     */
    public function verifyBackup($filename);
}
