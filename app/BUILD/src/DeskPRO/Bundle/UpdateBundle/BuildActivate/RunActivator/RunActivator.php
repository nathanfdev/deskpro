<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\UpdateBundle\BuildActivate\RunActivator;

use Alchemy\Zippy\Zippy;
use DeskPRO\Bundle\UpdateBundle\Instance\BuildInstance;
use DeskPRO\Component\Filesystem\TmpDir;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Filesystem\Filesystem;

class RunActivator implements RunActivatorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var Zippy
     */
    private $zippy;

    /**
     * @var string
     */
    private $tmpDir;

    /**
     * RunActivator constructor.
     *
     * @param Zippy                $zippy
     * @param                      $tmpDir
     * @param LoggerInterface|null $logger
     */
    public function __construct(Zippy $zippy, $tmpDir, LoggerInterface $logger = null)
    {
        $this->zippy  = $zippy;
        $this->tmpDir = $tmpDir;
        $this->setLogger($logger ?: new NullLogger());
    }

    /**
     * @param BuildInstance $build
     *
     * @throws \Exception
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     */
    public function activateRunDir(BuildInstance $build)
    {
        $runPath    = dirname($build->getAppPath()).'/run';
        $oldRunPath = $runPath.'.'.uniqid('');

        if (file_exists($runPath.'/build-num.txt')) {
            $n = trim(@file_get_contents($runPath.'/build-num.txt'));
            if ($n == $build->getBuildId()) {
                $this->logger->info('The run directory is already using files from te requested build');

                return;
            }
        }

        $fs = new Filesystem();

        $newRunPath = $build->getKernelCachePath().DIRECTORY_SEPARATOR.'dp_run';

        // The cached dp_run dir doesnt exist for whatever reason, we need to re-extract the full zip
        if (!is_dir($newRunPath)) {
            $this->logger->info('The default run path from kernel cache doesnt exist, we will need to re-extract');

            try {
                $scratchDir = TmpDir::makeTmpDir($this->tmpDir);
                $this->logger->info("Extracting into temp scratch dir: $scratchDir");

                $zip = $this->zippy->open($build->getAppPath().'/sys/Resources/deskpro.zip');
                $zip->extract($scratchDir);
            } catch (\Exception $e) {
                $this->logger->error('Failed to extract: '.$e->getMessage());
                throw $e;
            }

            $newRunPath = "$scratchDir/app/run";
        }

        $this->logger->info('Existing run dir will be moved to: '.$oldRunPath);

        $moves = [
            $runPath    => $oldRunPath,
            $newRunPath => $runPath,
        ];

        foreach ($moves as $from => $to) {
            try {
                $this->logger->info("Move: $from => $to");
                $fs->rename($from, $to);
            } catch (\Exception $e) {
                $this->logger->error('Failed fs move: '.$e->getMessage());
                throw $e;
            }
        }

        // Remove the old one
        try {
            $fs->remove($oldRunPath);
        } catch (\Exception $e) {
            $this->logger->warning('There was a problem removing the old build files: '.$e->getMessage());
            // We just ignore an exception here.
            // It is unlikely to occur, and it's not a
            // big issue if it does.
        }
    }
}
