<?php

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
