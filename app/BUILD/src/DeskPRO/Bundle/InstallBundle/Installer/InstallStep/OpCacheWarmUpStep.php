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

use Symfony\Component\Process\ProcessBuilder;

class OpCacheWarmUpStep extends AbstractStep
{
    public function run()
    {
        $this->writeBigTitle('OpCache warmup');
        $this->writeln('');

        if (extension_loaded('Zend OPcache')) {
            $this->writeln('We are about warming up your cache.');
            $this->writeln('');
            $builder = new ProcessBuilder([
                $this->getSession()->getPaths()->php_path,
                $this->getContext()->getDpEnv()->getDpRoot().'/bin/console',
                'dp:warmup-opcache',
                '--verbose',
            ]);

            $builder->setTimeout(10 * 60);

            $progress = $this->createProgressBar();
            $progress->setFormat('Initializing ... [%bar%]');
            $progress->setRedrawFrequency(1);
            $progress->setBarWidth(5);

            $proc = $builder->getProcess();

            $proc->start();
            $proc->wait(function () use ($progress) {
                $progress->advance();
            });

            $this->writeln('');

            if (!$proc->isSuccessful()) {
                $this->writeln('<error>Failed to warmup cache</error>');
                $this->markAsFailed();

                $this->writeln('<info>'.$proc->getCommandLine().'</info>');
                $this->writeln($proc->getOutput());
                $this->writeln($proc->getErrorOutput());

                return;
            }

            $this->writeln('Done!');
        } else {
            $this->writeln('<info>You have no enabled OpCache extension.</info>');
        }

        $this->getSession()->enableFlag('installer_done');
    }

    public function isComplete()
    {
        return false;
    }
}
