<?php

namespace DeskPRO\Bundle\InstallBundle\Installer\InstallStep;

use DeskPRO\Bundle\AppBundle\SoftwareService\StatService\StatEvent\InstallStartEvent;
use DeskPRO\Bundle\InstallBundle\Installer\InstallerContext;
use DeskPRO\Bundle\InstallBundle\InstallSession\Model\User;
use DpSys\LowError\SystemErrorHandler;
use Orb\Validator\StringEmail;
use Symfony\Component\Console\Question\Question;

class WelcomeStep extends AbstractStep
{
    /**
     * @var bool
     */
    private $skipAdmin;

    /**
     * {@inheritdoc}
     */
    public function __construct(InstallerContext $context, $skipAdmin = false)
    {
        parent::__construct($context);
        $this->skipAdmin = $skipAdmin;
    }

    public function run()
    {
        $this->writeBigTitle('DeskPRO Installer');
        $this->writeln('');

        $this->writeln('Welcome to the DeskPRO installer. This tool will interactively guide you through the install procedure.');
        $this->writeln('');

        $event = InstallStartEvent::create()
            ->setUuid($this->getContext()->getSession()->getSessionUuid())
            ->setBuild(DP_ACTIVE_BUILD)
        ;

        if (!$this->skipAdmin) {
            $this->writeln('Before we continue, please enter your name and email address. This will be used for your initial admin account that we will set up in a minute.');
            $this->writeln('');

            $user        = new User();
            $user->name  = $this->getUserName();
            $user->email = $this->getUserEmail();

            $event->setUserName($user->name)->setUserEmail($user->email);

            $this->getSession()->setUser($user);
        }

        $statService = $this->getContext()->getMainContainer(false)->get('dp.software_service.stats');
        SystemErrorHandler::tryRun(function () use ($statService, $event) {
            $statService->sendInstallStart($event);
        });
    }

    public function isComplete()
    {
        return $this->getSession()->getUser() !== null;
    }

    /**
     * @return string
     */
    private function getUserName()
    {
        $q = new Question('Name: ');
        $q->setValidator(function ($str) {
            if (strlen($str) < 2) {
                throw new \Exception('Please enter your name');
            }

            return $str;
        });

        $name = $this->askQuestion($q, 'user_name');

        return $name;
    }

    /**
     * @return string
     */
    private function getUserEmail()
    {
        $q = new Question('Email: ');
        $q->setValidator(function ($str) {
            if (!StringEmail::isValueValid($str)) {
                throw new \Exception('Please enter a valid email address');
            }

            return strtolower($str);
        });

        $email = $this->askQuestion($q, 'user_email');

        return $email;
    }
}
