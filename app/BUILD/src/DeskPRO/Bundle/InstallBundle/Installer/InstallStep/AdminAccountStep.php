<?php

namespace DeskPRO\Bundle\InstallBundle\Installer\InstallStep;

use Symfony\Component\Console\Question\Question;

class AdminAccountStep extends AbstractStep
{
    /**
     * @var string
     */
    private $set_password;

    public function run()
    {
        $this->writeBigTitle('Setup Admin Account');

        $this->writeln('We will now configure your first admin account.');
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
        $q->setHidden(true);

        while (true) {
            $this->set_password = $this->askQuestion($q, 'user_password');

            $this->writeln('Now type your password again to verify.');
            $pass2 = $this->askQuestion($q, 'user_password');

            if ($this->set_password === $pass2) {
                break;
            }

            $this->writeln('<error>Passwords did not match.</error>');
            $this->writeln('Try again.');
            $this->writeln('');
        }

        $container = $this->getContext()->getMainContainer();
        $em        = $container->get('doctrine')->getManager();

        /** @var \Application\DeskPRO\Entity\Person $admin */
        $admin = $em->createQuery('SELECT p FROM DeskPRO:Person p WHERE p.can_admin = true ORDER BY p.id ASC')->setMaxResults(1)->getOneOrNullResult();

        if (!$admin) {
            throw new \RuntimeException('Could not find initial admin user');
        }

        $admin->setPassword($this->set_password);
        $em->persist($admin);

        $admin->getPrimaryEmail()->setEmail($this->getSession()->getUser()->email);
        $admin->setName($this->getSession()->getUser()->name);
        $em->persist($admin->getPrimaryEmail());

        // And we need to delete that special label that is used to
        // trigger the set password prompt on admin welcome guide
        $admin->removeLabelByString('not_user');

        $em->flush();

        $this->getSession()->enableFlag('install_admin_ok');
    }

    public function isComplete()
    {
        return $this->getSession()->hasFlag('install_admin_ok');
    }
}
