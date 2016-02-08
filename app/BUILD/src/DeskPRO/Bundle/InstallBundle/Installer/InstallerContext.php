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
     * @var DpKernel
     */
    private $mainKernel;

    /**
     * InstallerContext constructor.
     *
     * @param \DpRun\DpEnv    $dpEnv
     * @param InstallSession  $session
     * @param OutputInterface $output
     * @param InputInterface  $input
     * @param HelperSet       $helperSet
     */
    public function __construct(\DpRun\DpEnv $dpEnv, InstallSession $session, OutputInterface $output, InputInterface $input, HelperSet $helperSet)
    {
        $this->dpEnv     = $dpEnv;
        $this->session   = $session;
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
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getMainContainer($reload = false)
    {
        if ($this->mainKernel) {
            if (!$reload) {
                return $this->mainKernel->getContainer();
            }

            $this->mainKernel->shutdown();
            $this->mainKernel = null;
        }

        $this->mainKernel = new DpKernel($this->getDpEnv());
        $this->mainKernel->boot();

        return $this->mainKernel->getContainer();
    }
}
