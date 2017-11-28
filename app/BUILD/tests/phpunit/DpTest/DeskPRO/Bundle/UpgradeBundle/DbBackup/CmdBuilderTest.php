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

/**
 * DeskPRO.
 */

namespace DpTest\DeskPRO\Bundle\UpdateBundle\DbBackup;

use DeskPRO\Bundle\UpdateBundle\DbBackup\CmdBuilder;
use DpTest\DeskProTestCase;

class CmdBuilderTest extends DeskProTestCase
{
    /**
     * @test
     */
    public function it_returns_correct_mysqldump()
    {
        $cmdBuilder = $this->getCmdBuilder();
        $dbInfo     = \DpRun\LowUtil::getMysqlInfoFromConfigArray([
            'host'     => 'localhost',
            'user'     => 'deskpro',
            'password' => 'p@ass',
            'dbname'   => 'dpdb',
        ]);

        $this->assertEquals(
            "'/usr/bin/mysqldump' -h localhost --port 3306 -u 'deskpro' -p'p@ass' --opt -Q --hex-blob --lock-tables=false --single-transaction 'dpdb' > '/example/dump.sql'",
            $cmdBuilder->getDumpCmd('/example/dump.sql', $dbInfo)
        );
    }

    /**
     * @test
     */
    public function it_returns_correct_mysqldump_win()
    {
        $cmdBuilder = new CmdBuilder('/usr/bin/mysql', 'C:\\mysql\\bin\\mysqldump');

        $dbInfo = \DpRun\LowUtil::getMysqlInfoFromConfigArray([
            'host'     => 'localhost',
            'user'     => 'deskpro',
            'password' => 'p@ass',
            'dbname'   => 'dpdb',
        ]);

        $this->assertEquals(
            "'C:\\mysql\\bin\\mysqldump' -h localhost --port 3306 -u 'deskpro' -p'p@ass' --opt -Q --hex-blob --lock-tables=false --single-transaction 'dpdb' > 'C:\\Program Files\\Deskpro\\backups\\example.sql'",
            $cmdBuilder->getDumpCmd('C:\\Program Files\\Deskpro\\backups\\example.sql', $dbInfo)
        );
    }

    /**
     * @test
     */
    public function it_returns_correct_mysqldump_port()
    {
        $cmdBuilder = $this->getCmdBuilder();
        $dbInfo     = \DpRun\LowUtil::getMysqlInfoFromConfigArray([
            'host'     => 'localhost:14406',
            'user'     => 'deskpro',
            'password' => 'p@ass',
            'dbname'   => 'dpdb',
        ]);

        $this->assertEquals(
            "'/usr/bin/mysqldump' -h localhost --port 14406 -u 'deskpro' -p'p@ass' --opt -Q --hex-blob --lock-tables=false --single-transaction 'dpdb' > '/example/dump.sql'",
            $cmdBuilder->getDumpCmd('/example/dump.sql', $dbInfo)
        );
    }

    /**
     * @test
     */
    public function it_returns_correct_mysqldump_socket()
    {
        $cmdBuilder = $this->getCmdBuilder();
        $dbInfo     = \DpRun\LowUtil::getMysqlInfoFromConfigArray([
            'host'     => 'unix_socket:/var/run/mysqld/mysqld.sock',
            'user'     => 'deskpro',
            'password' => 'p@ass',
            'dbname'   => 'dpdb',
        ]);

        $this->assertEquals(
            "'/usr/bin/mysqldump' --protocol=socket -S '/var/run/mysqld/mysqld.sock' -u 'deskpro' -p'p@ass' --opt -Q --hex-blob --lock-tables=false --single-transaction 'dpdb' > '/example/dump.sql'",
            $cmdBuilder->getDumpCmd('/example/dump.sql', $dbInfo)
        );
    }

    /**
     * @testWith [false, "'/usr/bin/mysqldump' -h localhost --port 3306 -u 'deskpro' -p'pass\"word!@$.,,<>--'\\''\\' --opt -Q --hex-blob --lock-tables=false --single-transaction 'dpdb' > '/example/dump.sql'"]
     *           [true, "\"/usr/bin/mysqldump\" -h localhost --port 3306 -u deskpro -p\"pass\"\"word\"^!\"@$.,,<>--'\\\\\" --opt -Q --hex-blob --lock-tables=false --single-transaction dpdb > \"/example/dump.sql\""]
     * @test
     */
    public function it_returns_correct_mysqldump_weird_chars($isWindowsMode, $expectedResult)
    {
        $cmdBuilder = $this->getCmdBuilder($isWindowsMode);
        $dbInfo     = \DpRun\LowUtil::getMysqlInfoFromConfigArray([
            'host'     => 'localhost',
            'user'     => 'deskpro',
            'password' => 'pass"word!@$.,,<>--\'\\',
            'dbname'   => 'dpdb',
        ]);

        $this->assertEquals(
            $expectedResult,
            $cmdBuilder->getDumpCmd('/example/dump.sql', $dbInfo)
        );
    }

    /**
     * @return CmdBuilder
     */
    private function getCmdBuilder($isWindowsMode = false)
    {
        $builderMock = $this
            ->getMockBuilder(CmdBuilder::class)
            ->setConstructorArgs(['/usr/bin/mysql', '/usr/bin/mysqldump'])
            ->setMethods(['isWindowsMode'])
            ->getMock();
        $builderMock->expects($this->any())->method('isWindowsMode')->willReturn($isWindowsMode);

        return $builderMock;
    }
}
