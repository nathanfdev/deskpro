<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\InstallBundle\Installer\InstallStep;

use DeskPRO\Bundle\InstallBundle\InstallSession\Model\DbInfo;
use DeskPRO\Component\Util\EnvUtils;
use Symfony\Component\Console\Question\Question;

class AcceptDatabaseStep extends AbstractStep
{
    public function run()
    {
        $this->writeBigTitle('Database Details');
        $this->writeln('');

        $f = $this->getFormatterHelper();

        $dbinfo = $this->getSession()->getDbInfo() ?: new DbInfo();
        $ok     = false;

        while (true) {
            $this->writeln($f->formatBlock('MySQL Host', 'question', true));
            $this->writeln('Please enter the server/hostname for your MySQL server. Examples:');
            if (!EnvUtils::isWindows()) {
                $this->writeln('  > localhost                          <info>(host name)</info>');
            }
            $this->writeln('  > mysql.example.com                  <info>(host name)</info>');
            $this->writeln('  > mysql.example.com:3307             <info>(host name with port)</info>');
            $this->writeln('  > 192.168.1.15                       <info>(ip address)</info>');
            $this->writeln('  > 192.168.1.15:33309                 <info>(ip address with port)</info>');
            if (!EnvUtils::isWindows()) {
                $this->writeln('  > unix_socket:/tmp/mysql.sock        <info>(*nix socket)</info>');
            }
            $this->writeln('');
            $dbinfo->host = $this->askQuestion($this->getHostQuestion($dbinfo->host));
            $this->writeln('');

            $this->writeln($f->formatBlock('MySQL User', 'question', true));
            $this->writeln('Please enter a MySQL user.');
            $this->writeln('');
            $dbinfo->user = $this->askQuestion($this->getUserQuestion($dbinfo->user));
            $this->writeln('');

            $this->writeln($f->formatBlock(sprintf('MySQL Password (for %s)', $dbinfo->user), 'question', true));
            $this->writeln('Please enter the password for the '.$dbinfo->user.' user.');
            $this->writeln('<info>(Note: Your input below will be hidden while you type it as a security precaution.)</info>');
            $dbinfo->password = $this->askQuestion($this->getPasswordQuestion($dbinfo->password));
            $this->writeln('');

            $this->writeln($f->formatBlock('MySQL Database Name', 'question', true));
            $this->writeln('Please enter the database name that DeskPRO should use.');
            $this->writeln('');
            $dbinfo->dbname = $this->askQuestion($this->getDbnameQuestion($dbinfo->dbname));
            $this->writeln('');

            $this->writeln($f->formatBlock('Checking database details', 'question', true));

            if (!$this->validateDbInfo($dbinfo, true)) {
                $this->writeln('');
                $this->writeln('The database details you entered appear to be incorrect. You will be prompted to re-enter your details.');
                $this->writeln('Press any key when you are ready...');
                fgetc(STDIN);
            } else {
                $this->writeln('');
                $this->writeln('<info>Success! Your database details appear to be correct.</info>');
                break;
            }
        }

        $this->getSession()->setDbInfo($dbinfo);
        $this->getSession()->enableFlag('reset_db_details');
    }

    private function validateDbInfo(DbInfo $dbinfo, $auto_create = false)
    {
        $conn_info = \DpRun\LowUtil::getMysqlInfoFromConfigArray([
            'host'     => $dbinfo->host,
            'user'     => $dbinfo->user,
            'password' => $dbinfo->password,
            'dbname'   => $dbinfo->dbname,
        ]);

        $is_db_error = false;
        $did_connect = false;

        try {
            $this->writeln('Checking connection ...');
            $pdo = new \PDO($conn_info['dsn'], $conn_info['user'], $conn_info['password']);
            $this->writeln('  > <info>OK</info>');
            $did_connect = true;

            $this->writeln('Checking version ...');
            $mysql_version = $pdo->query('SELECT VERSION()')->fetchColumn();

            if (
                strpos($mysql_version, 'MariaDB')
                || version_compare($mysql_version, '5.0', '>=')
            ) {
                $this->writeln('  > <info>OK</info>');
            } else {
                $this->writeln('  > <error>FAIL</error>');
                $this->writeln('<error>DeskPRO requires MySQL version 5.0 or newer.</error>');
                $is_db_error = true;
            }

            if (!$is_db_error) {
                $this->writeln('Checking to make sure the database is empty...');
                $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
                if (!$tables) {
                    $this->writeln('  > <info>OK</info>');
                } else {
                    $this->writeln('  > <error>FAIL</error>');
                    $this->writeln('<error>The database you install DeskPRO into must be completely empty.</error>');
                    $is_db_error = true;
                }
            }
        } catch (\Exception $e) {

            // Silently try to create the database ourselves
            if ($auto_create && !$did_connect) {
                try {
                    $conn_info = \DpRun\LowUtil::getMysqlInfoFromConfigArray([
                        'host'     => $dbinfo->host,
                        'user'     => $dbinfo->user,
                        'password' => $dbinfo->password,
                        'dbname'   => null,
                    ]);
                    $pdo = new \PDO($conn_info['dsn'], $conn_info['user'], $conn_info['password']);
                    $pdo->exec('CREATE DATABASE `'.$dbinfo->dbname.'`');

                    return $this->validateDbInfo($dbinfo);
                } catch (\Exception $e) {
                }
            }

            $this->writeln('  > <error>FAIL</error>');
            $this->writeln('<error>We encountered an error while trying to test your database:</error>');
            $this->writeln('<error>'.$e->getMessage().'</error>');
            $this->writeln('');
            $is_db_error = true;
        }

        return !$is_db_error;
    }

    /**
     * @param string|null $default
     *
     * @return Question
     */
    private function getHostQuestion($default = null)
    {
        $def_prompt = '';
        if ($default) {
            $def_prompt = ' ['.$default.']';
        }

        $q = new Question('MySQL Host'.$def_prompt.'> ', $default);
        $q->setValidator(function ($v) {
            $v = trim($v);
            if (!$v) {
                throw new \Exception('Please enter a host');
            }

            if (EnvUtils::isWindows() && strtolower($v) === 'localhost') {
                throw new \Exception('Due to bad handling of the localhost name on Windows causing very bad performance, DeskPRO does not allow it. You can try the loopback IP address instead (127.0.0.1).');
            }

            return $v;
        });

        return $q;
    }

    /**
     * @param string|null $default
     *
     * @return Question
     */
    private function getUserQuestion($default = null)
    {
        $def_prompt = '';
        if ($default) {
            $def_prompt = ' ['.$default.']';
        }

        $q = new Question('MySQL User'.$def_prompt.'> ', $default);
        $q->setValidator(function ($v) {
            $v = trim($v);
            if (!$v) {
                throw new \Exception('Please enter a user.');
            }

            if (strlen($v) > 32) {
                throw new \Exception('MySQL user names cannot be longer than 32 characters.');
            }

            return $v;
        });

        return $q;
    }

    /**
     * @param string|null $default
     *
     * @return Question
     */
    private function getPasswordQuestion($default = null)
    {
        $def_prompt = '';
        if ($default) {
            $def_prompt = ' ['.str_repeat('*', strlen($default)).']';
        }

        $q = new Question('MySQL Password'.$def_prompt.'> ', $default);
        $q->setHidden(true);
        $q->setHiddenFallback(true);
        $q->setValidator(function ($v) {
            $v = trim($v);
            if (!$v) {
                throw new \Exception('Please enter a password.');
            }

            return $v;
        });

        return $q;
    }

    /**
     * @param string|null $default
     *
     * @return Question
     */
    private function getDbnameQuestion($default = null)
    {
        $def_prompt = '';
        if ($default) {
            $def_prompt = ' ['.$default.']';
        }

        $q = new Question('MySQL Database Name'.$def_prompt.'> ', $default);
        $q->setValidator(function ($v) {
            $v = trim($v);
            if (!$v) {
                throw new \Exception('Please the name of the database.');
            }

            if (!preg_match('#^[a-zA-z0-9\-_\.]+#', $v)) {
                throw new \Exception('Database names must only contain: letters a-z, numbers 0-9, underscores, dashes, periods.');
            }

            return $v;
        });

        return $q;
    }

    public function isComplete()
    {
        return $this->getSession()->getDbInfo() !== null
            && !$this->getSession()->hasFlag('reset_db_details');
    }
}
