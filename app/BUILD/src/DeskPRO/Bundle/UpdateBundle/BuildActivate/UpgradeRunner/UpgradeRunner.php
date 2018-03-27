<?php

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
        $cmdName = 'dp:update-db';
        if ($build->getBuildId() <= 24133) {
            // the old name was dp:upgrade
            $cmdName = 'dp:upgrade';
        }

        $builder = new ProcessBuilder([
            $this->phpPath,
            $build->getAppPath().DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'console',
            $cmdName,
        ]);
        $builder->setTimeout(null);

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
