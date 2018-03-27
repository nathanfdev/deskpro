<?php

namespace DeskPRO\Bundle\InstallBundle\Installer\InstallStep;

use DeskPRO\Bundle\AppBundle\Util\BinariesPathValidator;
use DeskPRO\Bundle\InstallBundle\Installer\InstallerContext;
use DeskPRO\Bundle\InstallBundle\InstallSession\Model\Paths;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\PhpExecutableFinder;

class AcceptPathsStep extends AbstractStep
{
    /**
     * @var BinariesPathValidator
     */
    protected $validator;

    public function __construct(InstallerContext $context)
    {
        parent::__construct($context);
        $this->validator = new BinariesPathValidator();
    }

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
                    $path = $this->validator->validatePhpPath($path, $this->getContext()->getDpEnv()->getDpRoot());
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

        $dpRoot    = $this->getContext()->getDpEnv()->getDpRoot();
        $validator = $this->validator;

        $q = new Question('Enter \'php\' Path> ');
        $q->setValidator(function ($path) use ($validator, $dpRoot) {
            return $validator->validatePhpPath($path, $dpRoot);
        });
        $result = $this->askQuestion($q, 'path_php');

        $this->writeln('');
        $this->writeln('<info>Success! The path to PHP has been validated and is correct.</info>');
        $this->writeln('<info>Path set: '.$result.'</info>');
        $this->writeln('');

        return $result;
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
                    $path = $this->validator->validateMysqlPath($path);
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
        $q->setValidator([$this->validator, 'validateMysqlPath']);
        $result = $this->askQuestion($q, 'path_mysql');

        $this->writeln('');
        $this->writeln('<info>Success! The path to MySQL has been validated and is correct.</info>');
        $this->writeln('<info>Path set: '.$result.'</info>');
        $this->writeln('');

        return $result;
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
                    $path = $this->validator->validateMysqldumpPath($path);
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
        $q->setValidator([$this->validator, 'validateMysqldumpPath']);
        $result = $this->askQuestion($q, 'path_mysqldump');

        $this->writeln('');
        $this->writeln('<info>Success! The path to MySQL Dump has been validated and is correct.</info>');
        $this->writeln('<info>Path set: '.$result.'</info>');
        $this->writeln('');

        return $result;
    }

    public function isComplete()
    {
        return $this->getSession()->getPaths()
            && $this->getSession()->getPaths()->hasAllPaths();
    }
}
