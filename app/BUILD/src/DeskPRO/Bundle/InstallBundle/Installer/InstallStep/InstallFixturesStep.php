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
use Symfony\Component\Console\Question\Question;
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

        $this->writeln('We will now initialize the database and configure your first admin account.');
        $this->writeln('Your admin account is '.$this->getSession()->getUser()->email);
        $this->writeln('Please enter a password for your account.');
        $this->writeln('<info>(Note: Your input below will be hidden while you type it as a security precaution.)</info>');
        $this->writeln('');

        $q = new Question('Password> ');
        $q->setValidator(function ($v) {
            $v = trim($v);
            if (!$v || strlen($v) < 5) {
                throw new \Exception('Please enter a password of at least 5 characters');
            }

            return $v;
        });
        $this->set_password = $this->askQuestion($q);

        if (!$this->installFixtures()) {
            return;
        }
        $this->updateAdmin();

        $this->getSession()->enableFlag('install_fixtures_ok');
    }

    private function installFixtures()
    {
        $this->write('Installing default records ...');

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
            '--no-iteraction',
            '--append',
        ]);

        foreach ($fixtures as $f) {
            $builder->add('--fixtures')->add($path = DP_APP_DIR.'/src/DeskPRO/Bundle/AppBundle/DataFixtures/'.$f);
        }

        $proc = $builder->getProcess();
        $proc->run();

        if (!$proc->isSuccessful()) {
            $this->writeln('<error>Failed to initialize database</error>');
            $this->markAsFailed();

            $this->writeln($proc->getOutput());
            $this->writeln($proc->getErrorOutput());

            return false;
        }

        $this->writeln('OK');

        return true;
    }

    private function updateAdmin()
    {
        $this->write('Setting admin password ...');

        $container = $this->getContext()->getMainContainer();
        $em        = $container->get('doctrine')->getManager();

        /** @var \Application\DeskPRO\Entity\Person $admin */
        $admin = $em->createQuery('SELECT p FROM DeskPRO:Person p WHERE p.can_admin = true ORDER BY p.id ASC')->setMaxResults(1)->getOneOrNullResult();

        if (!$admin) {
            throw new \RuntimeException('Could not find initial admin user');
        }

        $admin->setPassword($input->getOption('admin-password'));
        $em->persist($admin);

        $admin->getPrimaryEmail()->setEmail($input->getOption('admin-email'));
        $em->persist($admin->getPrimaryEmail());

        // And we need to delete that special label that is used to
        // trigger the set password prompt on admin welcome guide
        $admin->removeLabelByString('not_user');

        $em->flush();
    }

    public function isComplete()
    {
        return $this->getSession()->hasFlag('install_fixtures_ok');
    }
}
