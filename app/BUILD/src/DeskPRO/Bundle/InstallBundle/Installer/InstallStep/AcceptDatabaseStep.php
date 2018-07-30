<?php

namespace DeskPRO\Bundle\InstallBundle\Installer\InstallStep;

use DeskPRO\Bundle\InstallBundle\Installer\InstallProfile;
use DeskPRO\Bundle\InstallBundle\InstallSession\Model\DbInfo;
use DeskPRO\Component\Util\EnvUtils;
use DeskPRO\Component\Util\StringUtils;
use Symfony\Component\Console\Question\Question;

class AcceptDatabaseStep extends AbstractStep
{
    public function run()
    {
        $this->writeBigTitle('Database Details');
        $this->writeln('');

        $dbinfo = $this->getSession()->getDbInfo() ?: new DbInfo();
        $dbs    = InstallProfile::getDbs();
        $info   = [];

        $advanced = $this->getContext()->getInput()->getOption('advanced');

        while (true) {
            foreach ($dbs as $db) {
                $info[$db] = $this->requestDbInfo($dbinfo, $db, $advanced, $db === 'db');
                if ($advanced && !$info[$db]) {
                    break;
                }
            }
            if (array_reduce($info, function ($carry, $db) {
                return $carry && $db;
            }, true)) {
                break;
            }
        }

        foreach ($info as $key => $dbinfo) {
            $method = StringUtils::toCamelCase(sprintf('set_%s_info', $key), false);
            $this->getSession()->{$method}($dbinfo);
        }

        $this->getSession()->disableFlag('reset_db_details');
        $this->getSession()->disableFlag('install_tables_ok');
        $this->getSession()->enableFlag('reset_config');
    }

    private function requestDbInfo($dbinfo, $db, $advanced, $defaultDb)
    {
        $f = $this->getFormatterHelper();

        if (!$defaultDb) {
            if ($advanced) {
                $this->writeln("Do you want to use default connection for $db?");
                if ($this->askConfirm(true)) {
                    return $dbinfo;
                } else {
                    $dbinfo = clone $dbinfo;
                }
            } else {
                return $dbinfo;
            }
        }

        $this->writeln($f->formatBlock('MySQL Host', 'question', true));

        if ($defaultDb) {
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
        }

        $dbinfo->host = $this->askQuestion($this->getHostQuestion($dbinfo->host), 'db_host');
        $this->writeln('');

        $this->writeln($f->formatBlock('MySQL User', 'question', true));
        $this->writeln('Please enter a MySQL user.');
        $this->writeln('');
        $dbinfo->user = $this->askQuestion($this->getUserQuestion($dbinfo->user), 'db_user');
        $this->writeln('');

        $this->writeln($f->formatBlock(sprintf('MySQL Password (for %s)', $dbinfo->user), 'question', true));
        $this->writeln('Please enter the password for the '.$dbinfo->user.' user.');
        $this->writeln('<info>(Note: Your input below will be hidden while you type it as a security precaution.)</info>');
        $dbinfo->password = $this->askQuestion($this->getPasswordQuestion($dbinfo->password), 'db_password');
        $this->writeln('');

        $this->writeln($f->formatBlock('MySQL Database Name', 'question', true));
        $this->writeln('Please enter the database name that DeskPRO should use.');
        $this->writeln('');
        $dbinfo->dbname = $this->askQuestion($this->getDbnameQuestion($dbinfo->dbname), 'db_dbname');
        $this->writeln('');

        $this->writeln($f->formatBlock('Checking database details', 'question', true));

        if (!$this->validateDbInfo($dbinfo, true)) {
            $this->writeln('');
            $this->writeln('The database details you entered appear to be incorrect. You will be prompted to re-enter your details.');
            $this->writeln('Press any key when you are ready...');
            fgetc(STDIN);

            return false;
        } else {
            $this->writeln('');
            $this->writeln('<info>Success! Your database details appear to be correct.</info>');

            return $dbinfo;
        }
    }

    private function validateDbInfo(DbInfo $dbinfo, $autoCreate = false)
    {
        $connInfo = \DpRun\LowUtil::getMysqlInfoFromConfigArray([
            'host'     => $dbinfo->host,
            'user'     => $dbinfo->user,
            'password' => $dbinfo->password,
            'dbname'   => $dbinfo->dbname,
        ]);

        $isDbError  = false;
        $didConnect = false;

        try {
            $this->writeln('Checking connection ...');
            $pdo = new \PDO($connInfo['dsn'], $connInfo['user'], $connInfo['password']);
            $this->writeln('  > <info>OK</info>');
            $didConnect = true;

            $this->writeln('Checking version ...');
            $mysqlVersion = $pdo->query('SELECT VERSION()')->fetchColumn();

            if (
                strpos($mysqlVersion, 'MariaDB')
                || version_compare($mysqlVersion, '5.0', '>=')
            ) {
                $this->writeln('  > <info>OK</info>');
            } else {
                $this->writeln('  > <error>FAIL</error>');
                $this->writeln('<error>DeskPRO requires MySQL version 5.0 or newer.</error>');
                $isDbError = true;
            }
        } catch (\Exception $e) {

            // Silently try to create the database ourselves
            if ($autoCreate && !$didConnect) {
                try {
                    $connInfo = \DpRun\LowUtil::getMysqlInfoFromConfigArray([
                        'host'     => $dbinfo->host,
                        'user'     => $dbinfo->user,
                        'password' => $dbinfo->password,
                        'dbname'   => null,
                    ]);
                    $pdo = new \PDO($connInfo['dsn'], $connInfo['user'], $connInfo['password']);
                    $pdo->exec('CREATE DATABASE `'.$dbinfo->dbname.'`');

                    return $this->validateDbInfo($dbinfo);
                } catch (\Exception $e) {
                }
            }

            $this->writeln('  > <error>FAIL</error>');
            $this->writeln('<error>We encountered an error while trying to test your database:</error>');
            $this->writeln('<error>'.$e->getMessage().'</error>');
            $this->writeln('');
            $isDbError = true;
        }

        return !$isDbError;
    }

    /**
     * @param string|null $default
     *
     * @return Question
     */
    private function getHostQuestion($default = null)
    {
        $defPrompt = '';
        if ($default) {
            $defPrompt = ' ['.$default.']';
        }

        $q = new Question('MySQL Host'.$defPrompt.'> ', $default);
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
        $defPrompt = '';
        if ($default) {
            $defPrompt = ' ['.$default.']';
        }

        $q = new Question('MySQL User'.$defPrompt.'> ', $default);
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
        $defPrompt = '';
        if ($default) {
            $defPrompt = ' ['.str_repeat('*', strlen($default)).']';
        }

        $q = new Question('MySQL Password'.$defPrompt.'> ', $default);
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
        $defPrompt = '';
        if ($default) {
            $defPrompt = ' ['.$default.']';
        }

        $q = new Question('MySQL Database Name'.$defPrompt.'> ', $default);
        $q->setValidator(function ($v) {
            $v = trim($v);
            if (!$v) {
                throw new \Exception('Please the name of the database.');
            }

            $v = str_replace('%DEV_RAND%', 'deskpro_dev_%RAND%', $v);
            $v = str_replace('%RAND%', date('YmdHi').'_'.mt_rand(1000, 9999), $v);

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
