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

namespace DeskPRO\Bundle\UpdateBundle\BuildActivate\UpgradeRunner;

use DeskPRO\Bundle\UpdateBundle\Instance\BuildInstance;
use DeskPRO\Component\Util\Buffer\LineBuffer;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Process\ProcessBuilder;

class UpgradeRunner implements UpgradeRunnerInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var string
     */
    private $phpPath;

    /**
     * UpgradeRunner constructor.
     *
     * @param                      $phpPath
     * @param LoggerInterface|null $logger
     */
    public function __construct($phpPath, LoggerInterface $logger = null)
    {
        $this->phpPath = $phpPath;
        $this->setLogger($logger ?: new NullLogger());
    }

    /**
     * @param BuildInstance $build
     *
     * @throws UpgradeRunnerException
     */
    public function runUpgrade(BuildInstance $build)
    {
        $builder = new ProcessBuilder([
            $this->phpPath,
            $build->getAppPath().DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'console',
            'dp:upgrade',
        ]);

        $proc = $builder->getProcess();
        $this->logger->info('[UpgradeRunner] command: '.$proc->getCommandLine());
        $logger = $this->logger;

        $buf = new LineBuffer(function ($out) use ($logger) {
            $logger->info($out, ['type' => 'commandOutput']);
        });
        $proc->run(function ($t, $out) use ($buf) {
            $buf->append($out);
        });
        $buf->flush();

        $this->logger->info('[UpgradeRunner] exit: '.$proc->getExitCode().' '.$proc->getExitCodeText());

        if (!$proc->isSuccessful()) {
            throw new UpgradeRunnerException('Upgrade command failed with error status: '.$proc->getExitCode().' '.$proc->getExitCodeText(), $proc->getExitCode());
        }
    }
}
