<?php

namespace DeskPRO\Bundle\InstallBundle\Installer;

use DeskPRO\Bundle\InstallBundle\InstallSession\InstallSession;
use DpSys\Kernel\DpKernel;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallerContext
{
    /**
     * @var \DpRun\DpEnv
     */
    private $dpEnv;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var HelperSet
     */
    private $helperSet;

    /**
     * @var InstallSession
     */
    private $session;

    /**
     * @var InstallProfile
     */
    private $profile;

    /**
     * @var DpKernel
     */
    private $mainKernel;

    /**
     * InstallerContext constructor.
     *
     * @param \DpRun\DpEnv    $dpEnv
     * @param InstallSession  $session
     * @param InstallProfile  $profile
     * @param OutputInterface $output
     * @param InputInterface  $input
     * @param HelperSet       $helperSet
     */
    public function __construct(\DpRun\DpEnv $dpEnv, InstallSession $session, InstallProfile $profile, OutputInterface $output, InputInterface $input, HelperSet $helperSet)
    {
        $this->dpEnv     = $dpEnv;
        $this->session   = $session;
        $this->profile   = $profile;
        $this->output    = $output;
        $this->input     = $input;
        $this->helperSet = $helperSet;
    }

    /**
     * @return \DpRun\DpEnv
     */
    public function getDpEnv()
    {
        return $this->dpEnv;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @return InputInterface
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @return HelperSet
     */
    public function getHelperSet()
    {
        return $this->helperSet;
    }

    /**
     * @return InstallSession
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @return InstallProfile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @param bool $reload
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getMainContainer($reload = true)
    {
        if ($this->mainKernel) {
            if (!$reload) {
                return $this->mainKernel->getContainer();
            }

            $this->mainKernel->shutdown();
            $this->mainKernel = null;
        }

        $this->getDpEnv()->resetConfigCache();
        $this->mainKernel = new DpKernel(
            $this->getDpEnv()->getEnvId(),
            $this->getDpEnv()->isDebug(),
            $this->getDpEnv()
        );
        $this->mainKernel->boot();

        // we are going to disable audit listener explicitly here, becasue
        // there are no command was called, so DisableListenerSubscriber is not handling event here
        $this->mainKernel->getContainer()->get('audit_log.doctrine_listener')->disableListener();

        return $this->mainKernel->getContainer();
    }
}
