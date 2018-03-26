<?php

namespace DeskPRO\Bundle\AppBundle\Command\Utility;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Blob;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DanglingBlobsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dp:utility:dangling-blobs')
            ->setDescription(<<<'DOC'
This tool helps you find dangling blobs. These are blob records not attached to any other record in the system.
This can happen if a database record was removed, but blobs attached to that record were not.

NOTE: It is not recommended you run this tool unless you know what you are doing. Some blobs are NOT referenced
anywhere (e.g. inline images within articles). So you should always run this with a --where clause where you 
can specify specific objects you want to clean.
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
            ->addOption('where', null, InputOption::VALUE_REQUIRED, 'A valid WHERE condition using the table reference `blobs`')
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
        $where       = $input->getOption('where') ?: '';
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
            $minId = $db->fetchColumn('SELECT id FROM blobs ORDER BY id ASC LIMIT 1');
        }
        if (!$maxId || $maxId === 'AUTO') {
            $maxId = $db->fetchColumn('SELECT id FROM blobs ORDER BY id DESC LIMIT 1');
        }

        $batchStart = $minId;
        $rangeCount = 0;
        $countFound = 0;
        $countAll   = 0;
        $cleanCount = 0;
        $totalSize  = 0;

        $parts = [];
        foreach (Blob::tablesWithBlobs() as $info) {
            foreach ($info['columns'] as $col) {
                $parts[] = "SELECT {$col} AS id FROM {$info['table']} WHERE {$col} IN (:query_ids)";
            }
        }

        $findQuerySql = implode("\nUNION\n", $parts);

        while ($batchStart < $maxId && $countAll < $limitScan && $countFound < $limit) {
            $batchEnd = ($batchStart + $batchSize) - 1;
            ++$rangeCount;

            if ($format === 'line') {
                $output->writeln("Scanning between $batchStart and $batchEnd");
            }

            $blobsInfo = $db->fetchAllKeyed('
                SELECT blobs.*
                FROM blobs
                WHERE blobs.id BETWEEN ? AND ?
                    '.($where ? " AND ($where) " : '').'
                ORDER BY blobs.id ASC
            ', [$batchStart, $batchEnd]);

            $blobIds = array_keys($blobsInfo);
            $countAll += count($blobIds);

            $danglingIds = [];
            $foundIdsMap = [];

            if ($blobIds) {
                foreach (array_chunk($blobIds, 150) as $scanIds) {
                    $findQuery = $db->executeQuery(str_replace(':query_ids', implode(',', $scanIds), $findQuerySql));

                    while ($r = $findQuery->fetch(\PDO::FETCH_COLUMN)) {
                        $foundIdsMap[$r] = true;
                    }
                }

                foreach ($blobIds as $checkId) {
                    if (!isset($foundIdsMap[$checkId])) {
                        $danglingIds[] = $checkId;
                    }
                }

                if ($danglingIds) {
                    foreach ($danglingIds as $danglingId) {
                        $info = $blobsInfo[$danglingId];

                        $status = 'Dangling';
                        $totalSize += $info['filesize'];

                        if ($delete) {
                            if ($this->cleanBlob($info)) {
                                ++$cleanCount;
                                $status = '<info>CLEANED</info>';
                            } else {
                                $status = '<error>FAILED TO CLEAN</error>';
                            }
                        }

                        switch ($format) {
                            case 'line':
                                $output->writeln(sprintf('%-9d %-30s %s', $danglingId, $blobsInfo[$danglingId]['filename'], $status));
                                break;
                            case 'id':
                                $output->writeln($danglingId);
                                break;
                            case 'comma':
                                $output->write($danglingId.',');
                                break;
                        }
                    }
                    $countFound += count($danglingIds);
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

    private function cleanBlob(array $blobInfo)
    {
        $bs = $this->getContainer()->get('blob.storage');

        return $bs->deleteBlobRow($blobInfo);
    }
}
