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

use DeskPRO\Bundle\InstallBundle\InstallSession\InstallSession;
use Symfony\Component\Process\ProcessBuilder;

class InstallFixturesStep extends AbstractStep
{
    /**
     * @var string
     */
    private $set_password;

    public function run()
    {
        $this->writeBigTitle('Initializing database');

        $fixtures = [
            'SeedFixtures',
            'InstallFixtures',
        ];

        $is_dev = $this->getSession()->getSource() === InstallSession::SOURCE_DEV
            || $this->getSession()->getSource() === InstallSession::SOURCE_BUILDSERVER;

        if ($is_dev) {
            $fixtures[] = 'DevFixtures';
        }

        $builder = new ProcessBuilder([
            $this->getSession()->getPaths()->php_path,
            $this->getContext()->getDpEnv()->getDpRoot().'/bin/console',
            'doctrine:fixtures:load',
            '--no-interaction',
            '--append',
            '--verbose',
        ]);

        foreach ($fixtures as $f) {
            $builder->add('--fixtures')->add($path = DP_APP_DIR.'/src/DeskPRO/Bundle/AppBundle/DataFixtures/'.$f);
        }

        $proc = $builder->getProcess();
        $proc->run();

        if (!$proc->isSuccessful()) {
            $this->writeln('<error>Failed to initialize database</error>');
            $this->markAsFailed();

            $this->writeln('<info>'.$proc->getCommandLine().'</info>');
            $this->writeln($proc->getOutput());
            $this->writeln($proc->getErrorOutput());

            return false;
        }

        $this->writeln('Done!');

        $this->getSession()->enableFlag('install_fixtures_ok');
    }

    public function isComplete()
    {
        return $this->getSession()->hasFlag('install_fixtures_ok');
    }
}
