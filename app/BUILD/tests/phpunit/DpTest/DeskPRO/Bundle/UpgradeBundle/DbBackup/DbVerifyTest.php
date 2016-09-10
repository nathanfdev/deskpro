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
     * todo fix me.
     *
     * @expectedException \DeskPRO\Bundle\UpdateBundle\DbBackup\DbBackupException
     * @expectedExceptionCode  \DeskPRO\Bundle\UpdateBundle\DbBackup\DbBackupException::DUMP_ERROR_MISSING_TABLE
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
