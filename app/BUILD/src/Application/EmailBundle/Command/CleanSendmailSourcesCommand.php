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

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Command;

use Application\DeskPRO\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CleanSendmailSourcesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dp:email:clean-sendmail-sources')
            ->addArgument('start-date', InputArgument::REQUIRED, 'The START date')
            ->addArgument('end-date', InputArgument::OPTIONAL, 'The END date. If not specified, will be until now.')
            ->addOption('action', null, InputOption::VALUE_REQUIRED, 'The action to perform: delete, resend, abort', 'delete')
            ->addOption('run', null, InputOption::VALUE_NONE, 'Run the actions. Without this flag, its only a preview.')
            ->setHelp('Deletes sendmail source between two dates.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $db        = $container->get('database_connection');

        $start = strtotime($input->getArgument('start-date'));

        if ($input->getArgument('end-date')) {
            $end = strtotime($input->getArgument('end-date'));
        } else {
            $end = time();
        }

        if (!$start) {
            $output->writeln('<error>Invalid start date</error>');

            return 1;
        }
        if (!$end) {
            $output->writeln('<error>Invalid end date</error>');

            return 1;
        }

        $ids = $db->fetchAllCol('
            SELECT id
            FROM sendmail_sources
            WHERE date_created BETWEEN ? AND ?
            ORDER BY id ASC
        ', [date('Y-m-d H:i:s', $start), date('Y-m-d H:i:s', $end)]);

        if (!$input->getOption('run')) {
            $output->writeln(sprintf('Start:    %s', date('Y-m-d H:i:s', $start)));
            $output->writeln(sprintf('End:      %s', date('Y-m-d H:i:s', $end)));
            $output->writeln(sprintf('Action:   %s', $input->getOption('action')));
            $output->writeln(sprintf('Count:    %s', count($ids)));
            $output->writeln('');
            $output->writeln('Run this command again with the --run flag to perform the action.');

            return 0;
        }

        if (empty($ids)) {
            $output->writeln('No matches during the times you specified. Nothing to do.');

            return 0;
        }

        echo 'Running ';

        switch ($input->getOption('action')) {
            case 'resend':
                /** @var \Application\EmailBundle\SourceMapper\SourceMapperInterface $source_mapper */
                $source_mapper = $container->get('email.source_mapper');

                $recs = $db->fetchAll(
                    '
                    SELECT *
                    FROM sendmail_sources
                    WHERE id IN (?)
                ',
                    [$ids],
                    [Connection::PARAM_INT_ARRAY]
                );

                foreach ($recs as $r) {
                    $source_mapper->markSourceRetry(
                        $r,
                        sprintf(
                            '[%s] Manually marked for retry by %s',
                            date('Y-m-d H:i:s'),
                            'dp:email:clean-sendmail-sources'
                        )
                    );
                    echo '.';
                }
                break;

            case 'abort':
                /** @var \Application\EmailBundle\SourceMapper\SourceMapperInterface $source_mapper */
                $source_mapper = $container->get('email.source_mapper');

                $recs = $db->fetchAll(
                    "
                    SELECT *
                    FROM sendmail_sources
                    WHERE id IN (?) AND status IN ('pending', 'inserted', 'retry')
                ",
                    [$ids],
                    [Connection::PARAM_INT_ARRAY]
                );

                foreach ($recs as $r) {
                    $source_mapper->markSourceAborted(
                        $r,
                        sprintf('[%s] Manually aborted by %s', date('Y-m-d H:i:s'), 'dp:email:clean-sendmail-sources')
                    );
                    echo '.';
                }
                break;

            case 'delete':
                $bs   = $container->getBlobStorage();
                $recs = $db->fetchAll(
                    '
                    SELECT sendmail_sources.id AS sendmail_sources_id, blobs.*
                    FROM sendmail_sources
                    LEFT JOIN blobs ON blobs.id = sendmail_sources.blob_id
                    WHERE sendmail_sources.id IN ('.implode(',', $ids).')
                '
                );

                foreach ($recs as $r) {
                    $db->delete('sendmail_sources', ['id' => $r['sendmail_sources_id']]);
                    if ($r['id']) {
                        $bs->deleteBlobRow($r);
                    }
                    echo '.';
                }
                break;

            default:
                $output->writeln('<error>Invalid action</error>');

                return 1;
        }

        echo "\n\nDone\n";

        return 0;
    }
}
