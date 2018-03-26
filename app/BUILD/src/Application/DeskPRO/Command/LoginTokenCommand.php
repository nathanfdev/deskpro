<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Command;

use Orb\Util\Util;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LoginTokenCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('dp:login-token');
        $this->setHelp('Generates a temporary (valid for 5 mins) login token that can be used to login as any user');
        $this->addArgument('email', InputArgument::REQUIRED, 'The email address of the user');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');

        $person = $this->getContainer()->getEm()->getRepository('DeskPRO:Person')->findOneByEmail($email);

        if (!$person) {
            $output->writeln(sprintf('<error>Could not find any user with email address: %s</error>', $email));

            return 1;
        }

        $secret = sha1($person->secret_string.$person->salt);
        $token  = Util::generateStaticSecurityToken($secret, 300);

        $output->writeln('Log in with:');
        $output->writeln("<info>Email: $email</info>");
        $output->writeln("<info>Token: $token</info>");

        $router = $this->getContainer()->get('router');

        if ($person->is_agent) {
            if ($person->can_admin) {
                $url = $router->generate('user', [], UrlGeneratorInterface::ABSOLUTE_URL).'agent/login?return=/admin/&tok='.$person->getId().'-'.$token;
                $output->writeln("<info>Admin Quick Login: $url</info>");
            }

            $url = $router->generate('user', [], UrlGeneratorInterface::ABSOLUTE_URL).'agent/login?tok='.$person->getId().'-'.$token;
            $output->writeln("<info>Agent Quick Login: $url</info>");
        }

        $url = $router->generate('user', [], UrlGeneratorInterface::ABSOLUTE_URL).'login?tok='.$person->getId().'-'.$token;
        $output->writeln("<info>User Quick Login: $url</info>");

        $output->writeln('Note: This token will only work for the next 5 minutes.');
    }
}
