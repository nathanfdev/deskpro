<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Command;

use Application\DeskPRO\App;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VerifyBlobsCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('dp:verify-blobs');
        $this->setHelp('This checks all blobs stored on the filesystem to make sure they exist and that they are the correct');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bs = App::getContainer()->getBlobStorage();

        /** @var \Application\DeskPRO\BlobStorage\StorageAdapter\FilesystemStorage $fs */
        $fs = $bs->getAdapter('fs');

        $is_verbose = $output->getVerbosity() > 1;

        $page = 0;
        do {
            $limit      = $page++ * 1000;
            $blob_batch = App::getDb()->fetchAll("SELECT * FROM blobs WHERE storage_loc = 'fs' LIMIT $limit, 1000");

            foreach ($blob_batch as $blob) {
                $file_path = $fs->resolvePath($blob['save_path']);

                if (!file_exists($file_path)) {
                    printf("Blob #%d is MISSING. Expected: %s\n", $blob['id'], $file_path);
                } else {
                    $md5 = md5_file($file_path);
                    if ($md5 != $blob['blob_hash']) {
                        printf("Blob #%d is INVALID. Hash mismatch with file: %s\n\t$md5 != {$blob['blob_hash']}\n", $blob['id'], $file_path);
                    } else {
                        if ($is_verbose) {
                            printf("Blob #%d is OKAY. File: %s\n", $blob['id'], $file_path);
                        }
                    }
                }
            }

            if ($blob_batch) {
                printf("Checked %d files\n", count($blob_batch));
            }
        } while ($blob_batch);

        echo "\nDone\n";

        return 0;
    }
}
