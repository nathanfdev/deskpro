<?php

namespace DeskPRO\Bundle\AppBundle\Command\Utility;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Blob;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DanglingBlobStorageCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dp:utility:dangling-blob-storage')
            ->setDescription(<<<'DOC'
This tool helps you find dangling blob storage. That is, data that is written to storage but is not referenced to any
blob. This currently only scans the database adapter (blobs_storage table).
DOC
            )
            ->addOption('delete', null, InputOption::VALUE_NONE, 'Delete dangling blobs. Danger zone, this will permanently delete blobs!')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Options: line (one line written per danlging row), id or comma (only output id of dangling records, useful if you need a list for some other tool)', 'line')
            ->addOption('hide-summary', null, InputOption::VALUE_NONE, 'Dont show a summary at the end. This is always off for "id" format style.')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Stop processing when this many dangling blobs are found', 'NONE')
            ->addOption('limit-scan', null, InputOption::VALUE_REQUIRED, 'Stop scanning after this many records', 'NONE')
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Max records to scan at once', 1000)
            ->addOption('min-id', null, InputOption::VALUE_REQUIRED, 'Min blob ID to scan from', 'AUTO')
            ->addOption('max-id', null, InputOption::VALUE_REQUIRED, 'Max blob ID to scan from', 'AUTO')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $format      = strtolower($input->getOption('format'));
        $hideSummary = $input->getOption('hide-summary') || $format === 'id' || $format === 'comma';
        $delete      = $input->getOption('delete');
        $limit       = $input->getOption('limit') ?: 0;
        $limitScan   = $input->getOption('limit-scan') ?: 0;
        $batchSize   = $input->getOption('batch-size') ?: 500;
        $startTime   = microtime(true);

        if (!$limit || $limit === 'NONE') {
            $limit = PHP_INT_MAX;
        }
        if (!$limitScan || $limitScan === 'NONE') {
            $limitScan = PHP_INT_MAX;
        }

        /** @var Connection $db */
        $db = $this->getContainer()->getDbRead();

        $minId = $input->getOption('min-id');
        $maxId = $input->getOption('max-id');

        if (!$minId || $minId === 'AUTO') {
            $minId = $db->fetchColumn('SELECT id FROM blobs_storage ORDER BY id ASC LIMIT 1');
        }
        if (!$maxId || $maxId === 'AUTO') {
            $maxId = $db->fetchColumn('SELECT id FROM blobs_storage ORDER BY id DESC LIMIT 1');
        }

        $batchStart = $minId;
        $rangeCount = 0;
        $countFound = 0;
        $countAll   = 0;
        $cleanCount = 0;
        $totalSize  = 0;

        $findQuerySql = 'SELECT id FROM blobs WHERE id IN (:query_ids)';

        while ($batchStart < $maxId && $countAll < $limitScan && $countFound < $limit) {
            $batchEnd = ($batchStart + $batchSize) - 1;
            ++$rangeCount;

            if ($format === 'line') {
                $output->writeln("Scanning between $batchStart and $batchEnd");
            }

            $rowInfo = $db->fetchAllKeyed('
                SELECT id, blob_id, LENGTH(data) AS size
                FROM blobs_storage
                WHERE blob_id BETWEEN ? AND ?
                ORDER BY blob_id ASC
            ', [$batchStart, $batchEnd]);

            $blobIds = [];
            foreach ($rowInfo as $info) {
                $blobIds[] = $info['blob_id'];
            }
            $countAll += count($rowInfo);

            $foundIdsMap = [];

            if ($blobIds) {
                foreach (array_chunk($blobIds, 500) as $scanIds) {
                    $findQuery = $db->executeQuery(str_replace(':query_ids', implode(',', $scanIds), $findQuerySql));

                    while ($r = $findQuery->fetch(\PDO::FETCH_COLUMN)) {
                        $foundIdsMap[$r] = true;
                    }
                }

                foreach ($rowInfo as $info) {
                    if (isset($foundIdsMap[$info['blob_id']])) {
                        continue;
                    }
                    $status = 'Dangling';
                    $totalSize += $info['size'];

                    if ($delete) {
                        if ($this->cleanBlobStorage($info)) {
                            ++$cleanCount;
                            $status = '<info>CLEANED</info>';
                        } else {
                            $status = '<error>FAILED TO CLEAN</error>';
                        }
                    }

                    switch ($format) {
                        case 'line':
                            $output->writeln(sprintf('%-9d for missing blob %-9d %8d bytes    %s', $info['id'], $info['blob_id'], $info['size'], $status));
                            break;
                        case 'id':
                            $output->writeln($info['id']);
                            break;
                        case 'comma':
                            $output->write($info['id'].',');
                            break;
                    }
                    ++$countFound;
                }
            }

            $batchStart = $batchEnd;
        }

        if (!$hideSummary) {
            $output->writeln('');
            $output->writeln('Ranges scanned:           '.sprintf('%d (%d to %d, batch size %d)', $rangeCount, $minId, $maxId, $batchSize));
            $output->writeln('Records scanned:          '.$countAll);
            $output->writeln('Dangling records found:   '.sprintf('%d (%d bytes)', $countFound, $totalSize));
            $output->writeln('Dangling records cleaned: '.$cleanCount);
            $output->writeln('Time:                     '.sprintf('%.3fs', microtime(true) - $startTime));
        }

        return 0;
    }

    private function cleanBlobStorage(array $info)
    {
        $db = $this->getContainer()->get('database_connection');
        $db->delete('blobs_storage', ['id' => $info['id']]);

        return true;
    }
}
