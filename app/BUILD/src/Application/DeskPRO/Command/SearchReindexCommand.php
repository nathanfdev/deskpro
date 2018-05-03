<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SearchReindexCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('dp:search-reindex')->addArgument('content-type', InputArgument::REQUIRED, 'The type of content you want to reindex: article, download, feedback, news, topic, ticket');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $content_type = $input->getArgument('content-type');

        $table  = null;
        $entity = null;
        $ids    = null;

        switch ($content_type) {
            case 'article':
                $entity = 'DeskPRO:Article';
                $table  = 'articles';
                break;

            case 'download':
                $entity = 'DeskPRO:Download';
                $table  = 'downloads';
                break;

            case 'feedback':
                $entity = 'DeskPRO:Feedback';
                $table  = 'feedback';
                break;

            case 'news':
                $entity = 'DeskPRO:News';
                $table  = 'news';
                break;

            case 'topic':
                $entity = 'DeskPRO:Topic';
                $table  = 'topics';
                break;

            case 'ticket':
                $entity = 'DeskPRO:Ticket';
                $table  = 'tickets';
                break;

            default:
                $output->writeln("<warn>Unsupported content type `$content_type`</warn>");

                return 1;
                break;
        }

        $this->getContainer()->getSearchAdapter()->deleteContentTypeFromIndex($content_type);

        $all_ids = $this->getContainer()->getDb()->fetchAllCol("SELECT id FROM $table ORDER BY id ASC");
        if (!$all_ids) {
            $output->writeln('No objects to update.');

            return 0;
        }

        $all_batch_ids = array_chunk($all_ids, 20);

        $output->writeln(sprintf('%d objects will be processed in %d batches', count($all_ids), count($all_batch_ids)));

        //------------------------------
        // Process each
        //------------------------------

        $x = 0;
        foreach ($all_batch_ids as $batch_ids) {
            ++$x;
            $batch = $this->getContainer()->getEm()->getRepository($entity)->getByIds($batch_ids);
            if ($batch) {
                $this->getContainer()->getSearchAdapter()->updateObjectsInIndex($batch);
            }

            $this->getContainer()->getEm()->clear();

            $output->write('.');

            if ($x % 50 === 0) {
                set_time_limit(40);
                $this->getContainer()->getEm()->clear();
            }
        }
        $output->writeln('');

        return 0;
    }
}
