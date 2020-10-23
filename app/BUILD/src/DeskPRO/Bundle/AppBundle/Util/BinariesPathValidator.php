<?php

namespace DeskPRO\Bundle\AppBundle\Util;

use DeskPRO\Component\Util\EnvUtils;
use DpSys\SoftwareRequirements\DeskproRequirements;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class BinariesPathValidator
{
    /**
     * @param $path
     * @param $dpRoot
     *
     * @throws \Exception
     *
     * @return string
     */
    public function validatePhpPath($path, $dpRoot)
    {
        $path = $this->validateStandard($path);

        //------------------------------
        // Verify its php
        //------------------------------

        $builder = new ProcessBuilder([
            $path,
            '-v',
        ]);

        $proc = $builder->getProcess();
        $proc->run();

        if (!$proc->isSuccessful()) {
            $this->throwCmdVerifyError($proc);
        }

        $res   = $proc->getOutput();
        $match = 0;

        if (!preg_match('#^PHP\s+([\d\.]+)(.*?)?\s+\((.*?)\)#m', $res, $match)) {
            $this->throwCmdVerifyError($proc);
        }

        $sapi    = $match[3];
        $version = $match[1];

        if ($sapi !== 'cli') {
            print_r($res);
            print_r($match);
            throw new \Exception(
                'We detected that the binary you specified is not the path to the PHP command-line binary.'
                .'You cannot use the binary for PHP-FPM or CGI, it must be the CLI.'
                .'Check with this command: '.$proc->getCommandLine()
            );
        }

        if (version_compare($version, '5.5', '>=') < 1) {
            throw new \Exception(
                'We detected that the binary you specified is to an older version of PHP.'
                .'Check with this command: '.$proc->getCommandLine()
            );
        }

        //------------------------------
        // Verify requirements too
        //------------------------------

        $builder = new ProcessBuilder([
            $path,
            $dpRoot.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'check_requirements',
            '--encode-output',
        ]);

        $proc = $builder->getProcess();
        $proc->run();

        if (!$proc->isSuccessful()) {
            $this->throwCmdVerifyError($proc);
        }

        $res   = $proc->getOutput();
        $match = 0;

        if (!preg_match('#\-{10,}BEGIN\-{10,}(.*?)\-{10,}END\-{10,}#s', $res, $match)) {
            $this->throwCmdVerifyError($proc);
        }

        $checker = @unserialize(base64_decode(trim($match[1])));

        if (!$checker || !$checker instanceof DeskproRequirements) {
            $this->throwCmdVerifyError($proc);
        }

        if ($checker->getFailedRequirements()) {
            $msg = 'The path to PHP is valid, but it appears to be different from the version of PHP you are using to run this tool. '
                .'Server requirements did not pass on this separate version of PHP. You can get details by trying to run the command yourself: '
                .$proc->getCommandLine();
            throw new \Exception($msg);
        }

        return $path;
    }

    /**
     * @param $path
     *
     * @return string
     */
    public function validateMysqlPath($path)
    {
        $path = $this->validateStandard($path);

        //------------------------------
        // Verify its mysql
        //------------------------------

        $builder = new ProcessBuilder([
            $path,
            '--version',
        ]);

        $proc = $builder->getProcess();
        $proc->run();

        if (!$proc->isSuccessful()) {
            $this->throwCmdVerifyError($proc);
        }

        $res   = $proc->getOutput();
        $match = 0;

        // Output is like:
        // /some/path/mysql  Ver 15.1 Distrib 10.1.10-MariaDB, for osx10.11 (x86_64) using readline 5.1
        // From MariaDB 10.4.6, mariadbd is a symlink to mysqld
        // From MariaDB 10.5.2, mariadbd is the name of the binary, with mysqld a symlink

        if (!preg_match('#^([a-zA-Z0-9 \\/\.\-_:\\\\\(\)]*)(mysql|mariadb)(\.exe)?\s*#m', $res, $match)) {
            $this->throwCmdVerifyError($proc);
        }

        return $path;
    }

    /**
     * @param $path
     *
     * @return string
     */
    public function validateMysqldumpPath($path)
    {
        $path = $this->validateStandard($path);

        //------------------------------
        // Verify its mysqldump
        //------------------------------

        $builder = new ProcessBuilder([
            $path,
            '--version',
        ]);

        $proc = $builder->getProcess();
        $proc->run();

        if (!$proc->isSuccessful()) {
            $this->throwCmdVerifyError($proc);
        }

        $res   = $proc->getOutput();
        $match = 0;

        // Output is like:
        // /some/path/mysqldump  Ver 10.16 Distrib 10.1.10-MariaDB, for osx10.11 (x86_64)
        // From MariaDB 10.4.6, mariadb-dump is a symlink to mysqldump
        // From MariaDB 10.5.2, mariadb-dump is the name of the command-line client, with mysqldump a symlink

        if (!preg_match('#^([a-zA-Z0-9 \\/\.\-_:\\\\\(\)]*)(mysqldump|mariadb-dump)(\.exe)?\s*#m', $res, $match)) {
            $this->throwCmdVerifyError($proc);
        }

        return $path;
    }

    /**
     * @param $path
     *
     * @throws \Exception
     *
     * @return string
     */
    public function validateStandard($path)
    {
        if (!$path) {
            throw new \Exception('Please enter a path');
        }

        if (!file_exists($path)) {
            throw new \Exception('The path specified does not exist.');
        }

        if (is_dir($path)) {
            throw new \Exception('The path specified is a directory. Please enter the full path to an executable.');
        }

        $path = realpath($path);
        if (!$path || !is_file($path)) {
            if (EnvUtils::isWindows()) {
                throw new \Exception('Please enter the full path. I.e., including the drive letter like C:\\');
            } else {
                throw new \Exception("Please enter the full path from root. I.e., the path should begin with '/'.");
            }
        }

        if (!is_executable($path)) {
            throw new \Exception('The path specified exists, but is not an executable. Did you enter the full path?');
        }

        return $path;
    }

    /**
     * @param Process $proc
     *
     * @throws \Exception
     */
    protected function throwCmdVerifyError(Process $proc)
    {
        throw new \Exception(sprintf(
            "The path you entered appears to be invalid. We executed the following command as a test:\n%s\nThe command did not succeed. Output:\n%s\n",
            $proc->getCommandLine(),
            trim($proc->getOutput()."\n".$proc->getErrorOutput())
        ));
    }
}
