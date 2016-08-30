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

namespace DeskPRO\Bundle\InstallBundle\Installer\InstallStep;

use DeskPRO\Bundle\InstallBundle\InstallSession\Model\Paths;
use DeskPRO\Component\Util\EnvUtils;
use DpSys\SoftwareRequirements\DeskproRequirements;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class AcceptPathsStep extends AbstractStep
{
    public function run()
    {
        $this->writeBigTitle('Paths');
        $this->writeln('');
        $this->writeln('DeskPRO needs you to specify the full paths to a few utilities on your server:');
        $this->writeln('  - php');
        $this->writeln('  - mysql');
        $this->writeln('  - mysqldump');
        $this->writeln('');

        $paths = $this->getSession()->getPaths();
        if (!$paths) {
            $paths = new Paths();
            $this->getSession()->setPaths($paths);
        }

        if (!$paths->php_path) {
            if ($p = $this->determinePhpPath()) {
                $paths->php_path = $p;
            }
            $this->writeln('');
        }

        if (!$paths->mysql_path) {
            if ($p = $this->determineMysqlPath()) {
                $paths->mysql_path = $p;
            }
            $this->writeln('');
        }
        if (!$paths->mysqldump_path) {
            if ($p = $this->determineMysqldumpPath()) {
                $paths->mysqldump_path = $p;
            }
            $this->writeln('');
        }

        if (!$paths->hasAllPaths()) {
            $this->markAsFailed();
        }
    }

    /**
     * Get the path to php.
     *
     * @return string
     */
    private function determinePhpPath()
    {
        $this->writeln($this->getFormatterHelper()->formatBlock('Path to PHP', 'question', true));
        $this->writeln('DeskPRO requires the path to the PHP command-line bianry.');
        $this->writeln('This is required so DeskPRO can itself execute PHP commands (such as during an upgrade or cron job).');
        $this->writeln('');
        $this->writeln('You should make sure the path to PHP that you specify here is the same one as you are using to execute this installer tool.');
        $this->writeln('');

        $from_profile = $this->getContext()->getProfile()->getAnswer('path_php');
        if (!$from_profile || $from_profile === 'auto') {
            $finder = new PhpExecutableFinder();
            $path   = $finder->find(false);

            if ($path) {
                try {
                    $path = $this->validatePhpPath($path);
                    $this->writeln('<info>We detected the path to a PHP binary:</info>');
                    $this->writeln("<info>$path</info>");
                    $this->writeln('Do you want to use this path?');
                    if ($from_profile || $this->askConfirm(true)) {
                        return $path;
                    }
                } catch (\Exception $e) {
                }
            }
        }

        $q = new Question('Enter \'php\' Path> ');
        $q->setValidator([$this, 'validatePhpPath']);
        $result = $this->askQuestion($q, 'path_php');

        $this->writeln('');
        $this->writeln('<info>Success! The path to PHP has been validated and is correct.</info>');
        $this->writeln('<info>Path set: '.$result.'</info>');
        $this->writeln('');

        return $result;
    }

    /**
     * @internal
     *
     * @param string $path
     *
     * @throws \Exception
     *
     * @return string
     */
    public function validatePhpPath($path)
    {
        $path = $this->validateStandard($path);

        #------------------------------
        # Verify its php
        #------------------------------

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

        #------------------------------
        # Verify requirements too
        #------------------------------

        $builder = new ProcessBuilder([
            $path,
            $this->getContext()->getDpEnv()->getDpRoot().DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'check_requirements',
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
     * Get the path to mysql.
     *
     * @return string
     */
    private function determineMysqlPath()
    {
        $this->writeln($this->getFormatterHelper()->formatBlock('Path to MySQL', 'question', true));
        $this->writeln('DeskPRO requires the path to the MySQL client command-line utility.');
        $this->writeln('This is required so DeskPRO can manage database backups and low-level database commands.');
        $this->writeln('');

        $from_profile = $this->getContext()->getProfile()->getAnswer('path_mysql');
        if (!$from_profile || $from_profile === 'auto') {
            $finder = new ExecutableFinder();
            $path   = $finder->find('mysql');

            if ($path) {
                try {
                    $path = $this->validateMysqlPath($path);
                    $this->writeln('<info>We detected the path to a MySQL binary:</info>');
                    $this->writeln("<info>$path</info>");
                    $this->writeln('Do you want to use this path?');
                    if ($from_profile || $this->askConfirm(true)) {
                        return $path;
                    }
                } catch (\Exception $e) {
                }
            }
        }

        $q = new Question('Enter \'mysql\' Path> ');
        $q->setValidator([$this, 'validateMysqlPath']);
        $result = $this->askQuestion($q, 'path_mysql');

        $this->writeln('');
        $this->writeln('<info>Success! The path to MySQL has been validated and is correct.</info>');
        $this->writeln('<info>Path set: '.$result.'</info>');
        $this->writeln('');

        return $result;
    }

    /**
     * @internal
     *
     * @param string $path
     *
     * @throws \Exception
     *
     * @return string
     */
    public function validateMysqlPath($path)
    {
        $path = $this->validateStandard($path);

        #------------------------------
        # Verify its mysql
        #------------------------------

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

        if (!preg_match('#^([a-zA-Z0-9 \\/\.\-_:\\\\]*)mysql(\.exe)?\s*#m', $res, $match)) {
            $this->throwCmdVerifyError($proc);
        }

        return $path;
    }

    /**
     * Get the path to mysqldump.
     *
     * @return string
     */
    private function determineMysqldumpPath()
    {
        $this->writeln($this->getFormatterHelper()->formatBlock('Path to MySQL Dump', 'question', true));
        $this->writeln('DeskPRO requires the path to the mysqldump command-line utility.');
        $this->writeln('This is required so DeskPRO can make database backups.');
        $this->writeln('');

        $from_profile = $this->getContext()->getProfile()->getAnswer('path_mysqldump');
        if (!$from_profile || $from_profile === 'auto') {
            $finder = new ExecutableFinder();
            $path   = $finder->find('mysqldump');

            if ($path) {
                try {
                    $path = $this->validateMysqldumpPath($path);
                    $this->writeln('<info>We detected the path to a mysqldump binary:</info>');
                    $this->writeln("<info>$path</info>");
                    $this->writeln('Do you want to use this path?');
                    if ($from_profile || $this->askConfirm(true)) {
                        return $path;
                    }
                } catch (\Exception $e) {
                }
            }
        }

        $q = new Question('Enter \'mysqldump\' Path> ');
        $q->setValidator([$this, 'validateMysqldumpPath']);
        $result = $this->askQuestion($q, 'path_mysqldump');

        $this->writeln('');
        $this->writeln('<info>Success! The path to MySQL Dump has been validated and is correct.</info>');
        $this->writeln('<info>Path set: '.$result.'</info>');
        $this->writeln('');

        return $result;
    }

    /**
     * @internal
     *
     * @param string $path
     *
     * @throws \Exception
     *
     * @return string
     */
    public function validateMysqldumpPath($path)
    {
        $path = $this->validateStandard($path);

        #------------------------------
        # Verify its mysqldump
        #------------------------------

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

        if (!preg_match('#^([a-zA-Z0-9 \\/\.\-_:\\\\]*)mysqldump(\.exe)?\s*#m', $res, $match)) {
            $this->throwCmdVerifyError($proc);
        }

        return $path;
    }

    /**
     * @internal
     *
     * @param string $path
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

    private function throwCmdVerifyError(Process $proc)
    {
        throw new \Exception(sprintf(
            "The path you entered appears to be invalid. We executed the following command as a test:\n%s\nThe command did not succeed. Output:\n%s\n",
            $proc->getCommandLine(),
            trim($proc->getOutput()."\n".$proc->getErrorOutput())
        ));
    }

    public function isComplete()
    {
        return $this->getSession()->getPaths()
            && $this->getSession()->getPaths()->hasAllPaths();
    }
}
