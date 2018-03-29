<?php

namespace DeskPRO\Bundle\InstallBundle\Installer\InstallStep;

use Symfony\Component\Finder\Finder;

/**
 * A silent step that just checks to make sure we aren't installing DeskPRO
 * on top of some existing installation.
 */
class CheckExistingStep extends AbstractStep
{
    public function run()
    {
        $is_config_error    = $this->doesConfigExist();
        $is_attachdir_error = $this->doesAttachExist();

        if ($is_config_error || $is_attachdir_error) {
            $this->writeBigTitle('Existing Install');
            $this->writeln('<error>An existing installation was found here.</error>');
            $this->writeln('We have detected that an existing DeskPRO installation might already exist here.');
            $this->writeln('');

            $f = $this->getFormatterHelper();

            if ($is_config_error) {
                $this->writeln($f->formatSection(
                    'ERROR',
                    'Config files already exist. If you want to reinstall DeskPRO, you should delete the files in the config directory',
                    'error'
                ));
            }
            if ($is_attachdir_error) {
                $this->writeln($f->formatSection(
                    'ERROR',
                    'We detected that there are file attachments already uploaded to '
                        .'your attachments directory. If you want to reinstall DeskPRO, you should delete old attachments.',
                    'error'
                ));
            }

            $this->writeln('');
            $this->writeln('Correct the above problem(s) and try again.');
            $this->markAsFailed();
        } else {
            // all ok (no output)
            $this->getSession()->enableFlag('no_existing_hd');
        }
    }

    private function doesConfigExist()
    {
        if ($this->getContext()->getSession()->getSource() === 'dev'
            || $this->getContext()->getSession()->getSource() === 'buildserver'
        ) {
            $this->writeln('Dev mode. Skipping check on existing config (will be placed into backup dir if it exists).');

            return false;
        }

        $env = $this->getContext()->getDpEnv();

        $config_dir  = $env->getDpRoot().DIRECTORY_SEPARATOR.'config';
        $config_file = $config_dir.DIRECTORY_SEPARATOR.'config.database.php';

        return is_file($config_file);
    }

    private function doesAttachExist()
    {
        $env        = $this->getContext()->getDpEnv();
        $attach_dir = $env->getUserFilesDir();

        $subdirs = Finder::create()
            ->directories()
            ->in($attach_dir)
            ->depth(0);

        /** @var \SplFileInfo $d */
        foreach ($subdirs as $d) {

            // Assume if its a numbered sub-dir
            // that its an attach dir
            if (ctype_digit($d->getBasename())) {
                return true;
            }
        }

        return false;
    }

    public function isComplete()
    {
        return $this->getSession()->hasFlag('no_existing_hd');
    }
}
