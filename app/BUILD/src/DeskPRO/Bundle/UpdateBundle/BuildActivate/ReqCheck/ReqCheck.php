<?php

namespace DeskPRO\Bundle\UpdateBundle\BuildActivate\ReqCheck;

use DeskPRO\Bundle\UpdateBundle\Instance\BuildInstance;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Process\ProcessBuilder;

class ReqCheck implements ReqCheckInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var string
     */
    private $phpPath;

    /**
     * ReqCheck constructor.
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
     * {@inheritdoc}
     */
    public function assertValidRequirements(BuildInstance $build)
    {
        $builder = new ProcessBuilder([
            $this->phpPath,
            $build->getAppPath().DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'check_requirements',
            '--encode-json-array',
        ]);

        $proc = $builder->getProcess();
        $this->logger->info('ReqCheck command: '.$proc->getCommandLine());
        $proc->run();

        $this->logger->info('ReqCheck exit: '.$proc->getExitCode().' '.$proc->getExitCodeText());
        $out = $proc->getOutput();
        $this->logger->debug('ReqCheck result: '.$proc->getOutput());

        if (!$proc->isSuccessful()) {
            throw ReqCheckException::createProcessException($proc);
        }

        $decoder = new ReqCheckCommandDecoder();
        $results = $decoder->decodeResults($out);

        if (!empty($results['failed_requirements'])) {
            throw ReqCheckException::createFailedRequirementsException($results['failed_requirements']);
        }
    }
}
