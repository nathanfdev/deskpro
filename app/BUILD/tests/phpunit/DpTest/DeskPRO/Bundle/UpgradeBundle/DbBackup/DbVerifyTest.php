<?php

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\UpdateBundle\DbBackup;

use DeskPRO\Bundle\UpdateBundle\DbBackup\DbVerify;
use DpTest\DeskProTestCase;

class DbVerifyTest extends DeskProTestCase
{
    /**
     * @return DbVerify
     */
    private function getVerifier()
    {
        return new DbVerify();
    }

    /**
     * @test
     */
    public function it_accepts_valid_dump()
    {
        $tmpName = tempnam(sys_get_temp_dir(), 'test_dump');
        $fp      = fopen($tmpName, 'w');

        $s  = str_repeat('.', 120)."\n";
        $sl = strlen($s);

        for ($i = 0; $i <= DbVerify::MIN_SIZE; $i += $sl) {
            fwrite($fp, $s);
        }

        fwrite($fp, "\n\nCREATE TABLE `worker_jobs` (\n\n");
        fclose($fp);

        $this->assertTrue($this->getVerifier()->verifyBackup($tmpName));
    }

    /**
     * @ test
     * @ expectedException \DeskPRO\Bundle\UpdateBundle\DbBackup\DbBackupException
     * @ expectedExceptionCode  \DeskPRO\Bundle\UpdateBundle\DbBackup\DbBackupException::DUMP_ERROR_MISSING_TABLE
     */
    public function it_rejects_missing_worker_jobs()
    {
        $tmpName = tempnam(sys_get_temp_dir(), 'test_dump');
        $fp      = fopen($tmpName, 'w');

        $s  = str_repeat('.', 120)."\n";
        $sl = strlen($s);

        for ($i = 0; $i <= DbVerify::MIN_SIZE; $i += $sl) {
            fwrite($fp, $s);
        }

        $this->getVerifier()->verifyBackup($tmpName);
    }

    /**
     * @test
     * @expectedException \DeskPRO\Bundle\UpdateBundle\DbBackup\DbBackupException
     * @expectedExceptionCode  \DeskPRO\Bundle\UpdateBundle\DbBackup\DbBackupException::DUMP_ERROR_NOTFOUND
     */
    public function it_rejects_missing_file()
    {
        $this->getVerifier()->verifyBackup(sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid('bogus_file'));
    }

    /**
     * @test
     * @expectedException \DeskPRO\Bundle\UpdateBundle\DbBackup\DbBackupException
     * @expectedExceptionCode  \DeskPRO\Bundle\UpdateBundle\DbBackup\DbBackupException::DUMP_ERROR_TOOSMALL
     */
    public function it_rejects_small_file()
    {
        $tmpName = tempnam(sys_get_temp_dir(), 'test_dump');
        file_put_contents($tmpName, "\n\nCREATE TABLE `worker_jobs` (\n\n");
        $this->getVerifier()->verifyBackup($tmpName);
    }
}
