<?php

namespace DeskPRO\Bundle\AppBundle\Command\Utility;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\EmailSource;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportBlobCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dp:utility:export-blob')
            ->setDescription('Export a blob to stdout')
            ->addOption('info', 'i', InputOption::VALUE_NONE, 'Show info about the file instead of exporting it')
            ->addOption('nl', null, InputOption::VALUE_NONE, 'End with a newline')
            ->addArgument('blob', InputArgument::REQUIRED, 'A BlobID, or a string "email:SourceID" to export a raw email')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $blobIdent = $input->getArgument('blob');
        $blob      = null;

        $em = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $bs = $this->getContainer()->get('blob.storage');

        if (strpos($blobIdent, ':')) {
            list($type, $id) = explode(':', strtolower($blobIdent), 2);

            switch ($type) {
                case 'email':
                    /** @var EmailSource $emailSource */
                    $emailSource = $em->find(EmailSource::class, $id);
                    if (!$emailSource) {
                        $output->writeln("<error>Unknown email source: $id</error>");

                        return 1;
                    }
                    if (!$emailSource->blob) {
                        $output->writeln('<error>Email source has no blob</error>');

                        return 1;
                    }
                    $blob = $emailSource->blob;

                    if ($input->getOption('info')) {
                        $table = new Table($output);
                        $table->addRows([
                            ['EmailSourceID', $emailSource->id],
                            ['EmailAccount', $emailSource->email_account ? sprintf('%d :: %s', $emailSource->email_account->id, $emailSource->email_account->address) : 'null'],
                            ['Status', $emailSource->status],
                            ['Subject', $emailSource->header_subject],
                            ['To', $emailSource->header_to],
                            ['From', $emailSource->header_from],
                        ]);
                        $table->render();
                        echo "\n";
                    }
                    break;
                default:
                    $output->writeln("<error>Unknown blob type: $type</error>");

                    return 1;
            }
        } else {
            $blob = $em->find(Blob::class, $blobIdent);
            if (!$blob) {
                $output->writeln("<error>Unknown blob: $blobIdent</error>");

                return 1;
            }
        }

        if ($input->getOption('info')) {
            $table = new Table($output);
            $table->addRows([
                ['BlobID', $blob->id],
                ['File Name', $blob->filename],
                ['File Size', $blob->filesize],
                ['Type', $blob->content_type],
                ['Date Created', $blob->date_created->format('Y-m-d H:i:s')],
                ['Storage', trim($blob->storage_loc.' '.$blob->save_path)],
                ['URL', $blob->getDownloadUrl()],
            ]);
            $table->render();

            return 0;
        }

        echo $bs->copyBlobRecordToString($blob);
        if ($input->getOption('nl')) {
            echo "\n";
        }

        return 0;
    }
}
