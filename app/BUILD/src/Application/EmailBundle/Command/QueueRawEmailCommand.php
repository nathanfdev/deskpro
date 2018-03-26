<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Command;

use Application\EmailBundle\Mail\RawMessage\RawMessageUtil;
use Application\EmailBundle\Mail\RawMessage\Rfc2822Decoder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class QueueRawEmailCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dp:email:queue-raw-email');
        $this->addOption('as-pending', 'g', InputOption::VALUE_NONE, 'Insert the source as PENDING instead of ABORTED.');
        $this->addOption('save-exact', 'k', InputOption::VALUE_NONE, 'Save this exact raw source (i.e., do not decode+re-encode)');
        $this->addArgument('file', InputArgument::OPTIONAL);
        $this->setHelp('Reads a raw email from a file inserts it into the system.');
    }

    /**
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    public function getContainer()
    {
        return parent::getContainer();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $save_exact  = $input->getOption('save-exact');
        $use_blob_id = null;

        if ($input->getArgument('file')) {
            $path = realpath($input->getArgument('file'));

            if (!$path) {
                $output->writeln('<error>File does not exist</error>');

                return 1;
            }

            $raw_stream = fopen($path, 'r');
            if (!$raw_stream) {
                $output->writeln('<error>Could not open file</error>');

                return 1;
            }

            if ($save_exact) {
                $use_blob_id = $this->getContainer()->getBlobStorage()->createBlobRowFromFile($path, 'email.eml', 'message/rfc822');
                $use_blob_id = $use_blob_id['id'];
            }
        } else {
            $in_stream = STDIN;
            if (!$in_stream) {
                $output->writeln('<error>No source file provided</error>');

                return 1;
            }

            $raw_stream = tmpfile();
            stream_copy_to_stream($in_stream, $raw_stream);
            rewind($raw_stream);

            if ($save_exact) {
                $use_blob_id = $this->getContainer()->getBlobStorage()->createBlobRowFromString(stream_get_contents($raw_stream), 'email.eml', 'message/rfc822');
                $use_blob_id = $use_blob_id['id'];
                rewind($raw_stream);
            }
        }

        $decoder = new Rfc2822Decoder();
        $raw     = $decoder->createRawMessage($raw_stream);
        $message = RawMessageUtil::applyRawToSwift($raw);

        $mailer = $this->getContainer()->getMailer();

        if ($input->getOption('as-pending')) {
            $id = $mailer->queueMessage($message);
            $output->writeln("<info>Inserted email source: $id</info>\n");

            $output->writeln("The email was inserted with the 'pending' state and WILL BE SENT on the next queue run.");
        } else {
            $id = $mailer->insertMessage($message);

            $this->getContainer()->getDb()->update('sendmail_sources', [
                'status' => 'aborted',
            ], ['id' => $id]);

            $output->writeln("<info>Inserted email source: $id</info>\n");
            $output->writeln("The email was inserted with the 'aborted' state and will not be sent automatically.");
            $output->writeln('You may with to manually send this email using the following command:');
            $output->writeln("\tbin/console dp:email:sendsource $id\n");
        }

        if ($use_blob_id) {
            $old_blob_id = $this->getContainer()->getDb()->fetchColumn('SELECT blob_id FROM sendmail_sources WHERE id = ?', [$id]);
            $this->getContainer()->getDb()->update('sendmail_sources', ['blob_id' => $use_blob_id], ['id' => $id]);
            $this->getContainer()->getBlobStorage()->deleteBlobRowId($old_blob_id);
        }

        return 0;
    }
}
