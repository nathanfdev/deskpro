<?php

namespace DeskPRO\Bundle\UpdateBundle\Command;

use DeskPRO\Bundle\UpdateBundle\Session\SessionStep\SessionStep;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateStatusCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('dp:update:status-watch')
            ->addOption('session-id', null, InputOption::VALUE_REQUIRED, '(internal)')
            ->setDescription('Watches an existing automatic updater to show you the current status');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sessionId = $input->getOption('session-id');
        if (!$sessionId) {
            $output->writeln('<error>Please provide a session id</error>');

            return 1;
        }

        // Check if it exists
        $tmpPath = $this->getContainer()->get('deskpro.app_env')->getUserTmpDir();

        $path = $tmpPath.DIRECTORY_SEPARATOR.'update-session.'.$sessionId.'.dat';

        if (!is_file($path)) {
            $output->writeln('<error>No updater session with that ID has been started.</error>');

            return 1;
        }

        $smf = $this->getContainer()->get('dp.updater.session_manager_factory');
        $smf->enableSessionId($sessionId);

        $lastTick   = null;
        $lastStepId = null;

        $prog = null;

        $session = $smf->getManager()->getSession();
        foreach ($session->getStepIds() as $stepId) {
            $step = $session->getStep($stepId);
            if ($step->isFinished()) {
                $prog = $this->createProgressBar($output, $step->getTitle());
                $this->finishProgressBar($prog);
                $this->writeStepOutput($output, $step);
            }
        }

        while (1) {
            $session = $smf->getManager()->reloadSession();
            if ($lastTick === $session->getTick()) {
                if ($prog) {
                    $prog->advance();
                }
                usleep(400000);
                continue;
            }

            $lastTick = $session->getTick();

            if ($session->isWaiting()) {
                if ($prog === null) {
                    $prog = $this->createProgressBar($output, 'Waiting to begin');
                    $prog->display();
                }
                $prog->advance();
            } elseif ($session->isRunning()) {
                $currentStepId = $session->findCurrentStepId();
                if ($currentStepId !== $lastStepId) {
                    if ($lastStepId) {
                        if ($prog) {
                            $this->finishProgressBar($prog);
                            $prog = null;
                        }
                        $this->writeStepOutput($output, $session->getStep($lastStepId));
                    }

                    $lastStepId = $currentStepId;
                    if ($currentStepId) {
                        $currentStep = $session->getStep($currentStepId);
                        $prog        = $this->createProgressBar($output, $currentStep->getTitle());
                        $prog->display();
                    }
                } else {
                    if ($prog) {
                        $prog->advance();
                    }
                }
            } elseif ($session->isFinished()) {
                if ($lastStepId) {
                    $lastStep = $session->getStep($lastStepId);
                    if ($prog) {
                        if ($lastStep->isWaiting()) {
                            $prog->clear();
                            $prog = null;
                        } else {
                            if ($lastStep->isError()) {
                                $this->finishProgressBarFailure($prog);
                            } else {
                                $this->finishProgressBar($prog);
                            }
                            $prog = null;
                        }
                    }
                    $this->writeStepOutput($output, $session->getStep($lastStepId));
                }

                $this->writeStepOutput($output, $session);
                break;
            }
        }
    }

    /**
     * @param OutputInterface $output
     * @param SessionStep     $step
     */
    private function writeStepOutput(OutputInterface $output, SessionStep $step)
    {
        $output->writeln('');
        $output->writeln($step->getSummary());
        if ($step->getDetails()) {
            $output->writeln($step->getDetails());
        }
        $output->writeln('');
    }

    /**
     * @param OutputInterface $output
     * @param                 $title
     *
     * @return ProgressBar
     */
    private function createProgressBar(OutputInterface $output, $title)
    {
        $title = sprintf('%-30s', $title);

        $prog = new ProgressBar($output);
        $prog->setBarWidth(5);
        $prog->setRedrawFrequency(1);
        $prog->setMessage($title);
        $prog->setFormat('<info>%message% (%bar%)</info>');

        return $prog;
    }

    /**
     * @param ProgressBar $prog
     */
    private function finishProgressBar(ProgressBar $prog)
    {
        $prog->setFormat('%message% ✔');
        $prog->finish();
        $prog->display();
    }

    /**
     * @param ProgressBar $prog
     */
    private function finishProgressBarFailure(ProgressBar $prog)
    {
        $prog->setFormat('%message% !!!');
        $prog->finish();
        $prog->display();
    }
}
