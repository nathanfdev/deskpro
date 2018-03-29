<?php

namespace DeskPRO\Bundle\InstallBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class CleanCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('install:clean')
            ->addOption('keep-config', null, InputOption::VALUE_NONE, 'Keep config files')
            ->setDescription('This command will help you completely clean/delete DeskPRO from the server. You will be shown exactly what will be deleted, and then you will be asked to confirm.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dpEnv = $this->getContainer()->get('deskpro.low_dp_env');

        /** @var \Symfony\Component\Console\Helper\QuestionHelper $asker */
        $asker = $this->getHelper('question');

        $keepConfig = $input->getOption('keep-config');

        //--------------------------------------------------
        // Info
        //--------------------------------------------------

        $dbName = $dpEnv->getConfig('database.dbname');
        $dirs   = [
            'config' => [
                'path'        => $dpEnv->getDpRoot().DIRECTORY_SEPARATOR.'config',
                'description' => 'Contains configuration including database access details.',
            ],
            'tmp' => [
                'path'        => $dpEnv->getUserTmpDir(),
                'description' => 'Temporary data files such as partial file uploads.',
            ],
            'cache' => [
                'path'        => $dpEnv->getUserCacheDir(),
                'description' => 'Application cache files such as pre-rendered portal pages.',
            ],
            'debug' => [
                'path'        => $dpEnv->getUserDebugDir(),
                'description' => 'Application debug files such as diagnostics that might have been used by a support agent',
            ],
            'logs' => [
                'path'        => $dpEnv->getUserLogsDir(),
                'description' => 'Application log files such as error logs or updater logs',
            ],
            'backups' => [
                'path'        => $dpEnv->getUserBackupsDir(),
                'description' => 'Backup files such as database backups made by the automatic updater',
                'warning'     => 'It is recommended you copy or move any backups from this directory in case you need them.',
            ],
            'files' => [
                'path'        => $dpEnv->getUserFilesDir(),
                'description' => 'Files and attachments that were saved in DeskPRO, such as ticket attachments and images.',
                'warning'     => 'These files include any kind of file added to DeskPRO, including any kind of email or ticket attachment. You may wish to back them up, especially if you plan on keeping a database backup with the intention of having a backup capable of restoration.',
            ],
        ];

        $output->writeln("Here is a list of resources that this tool will remove:\n");

        $table = new Table($output);
        $table->setStyle('borderless');
        $table->addRow([
            'Database',
            sprintf("DB Name: %s\n<comment>All tables in this database will be dropped.</comment>", $dpEnv->getConfig('database.dbname')),
        ]);
        $rmPaths = [];
        foreach ($dirs as $id => $d) {
            if ($id === 'config' && $keepConfig) {
                continue;
            }

            $numFiles = Finder::create()
                ->in($d['path'])
                ->notName('.gitkeep')
                ->files()
                ->count();

            if (!$numFiles) {
                continue;
            }

            $rmPaths[$id] = $d['path'];

            $table->addRow(new TableSeparator());

            $title   = "$id dir";
            $message = sprintf("Full Path: %s\n<comment>Number of files to be deleted: %d</comment>\n%s", $d['path'], $numFiles, $d['description']);
            if (!empty($d['warning'])) {
                $message .= "\n<info>{$d['warning']}</info>";
            }

            $table->addRow([$title, $message]);
        }

        $table->render();

        //--------------------------------------------------
        // Summary
        //--------------------------------------------------

        $output->writeln('Are you ready to see a summary of actions? (You will still have a chance to abort).');
        $question = new ConfirmationQuestion('[y/n]> ');
        if (!$asker->ask($input, $output, $question)) {
            $output->writeln('<error>OK, aborting</error>');

            return 1;
        }

        $output->writeln('');
        $output->writeln('<comment>Here is a list of actions we will perform:</comment>');
        $output->writeln(' mysql> DROP DATABASE `'.$dbName.'`');
        foreach ($rmPaths as $path) {
            $output->writeln(' $ rm -rf '.$path);
        }

        $output->writeln('');
        $output->writeln('<error>Do you want to apply these actions now?</error> This will immediately result in your helpdesk data being deleted.');

        $question = new ConfirmationQuestion('Type exactly: apply now> ', false, '/^apply now$/i');
        if (!$asker->ask($input, $output, $question)) {
            $output->writeln('<error>OK, aborting</error>');

            return 1;
        }

        //--------------------------------------------------
        // Perform
        //--------------------------------------------------

        $pdo = \DpRun\LowUtil::getPdoFromMysqlInfo($dpEnv->getConfig('database'));

        $output->write("Deleting tables in database $dbName ... ");
        $q      = $pdo->query('SHOW TABLES');
        $tables = $q->fetchAll(\PDO::FETCH_COLUMN);

        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($tables as $t) {
            $pdo->exec("DROP TABLE `$t`");
            $output->write('.');
        }
        $output->writeln(' OK');

        $fs = new Filesystem();

        foreach ($rmPaths as $id => $path) {
            if ($id === 'config' && $keepConfig) {
                continue;
            }

            $output->write("Removing files in $path ... ");
            try {
                $fs->remove($path);
                $output->writeln('OK');

                if (!preg_match('/config$/', $path)) {
                    try {
                        // recreate the empty dir
                        $fs->mkdir($path);
                        $fs->chmod($path, 0777);
                    } catch (\Exception $e) {
                    }
                }
            } catch (\Exception $e) {
                $output->writeln('Failed');
                $output->writeln($e->getMessage());
            }
        }
    }
}
