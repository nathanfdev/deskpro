<?php

namespace Application\DeskPRO\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Log\Logger;

class SearchReindexCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('dp:search-reindex')->addArgument('content-type', InputArgument::REQUIRED, 'The type of content you want to reindex: article, download, feedback, news, ticket');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$content_type = $input->getArgument('content-type');

		$table = null;
		$entity = null;
		$ids = null;

		switch ($content_type) {
			case 'article':
				$entity = 'DeskPRO:Article';
				$table = 'articles';
				break;

			case 'download':
				$entity = 'DeskPRO:Download';
				$table = 'downloads';
				break;

			case 'feedback':
				$entity = 'DeskPRO:Idea';
				$table = 'feedback';
				break;

			case 'news':
				$entity = 'DeskPRO:News';
				$table = 'news';
				break;

			default:
				$output->writeln("<warn>Unsupported content type `$content_type`</warn>");
				return 1;
				break;
		}

		$this->getContainer()->getSearchAdapter()->deleteContentTypeFromIndex($content_type);

		$all_ids = $this->getContainer()->getDb()->fetchAllCol("SELECT id FROM $table ORDER BY id ASC");
		if (!$all_ids) {
			$output->writeln("No objects to update.");
			return 0;
		}

		$all_batch_ids = array_chunk($all_ids, 20);

		$output->writeln(sprintf("%d objects will be processed in %d batches", count($all_ids), count($all_batch_ids)));

		#------------------------------
		# Process each
		#------------------------------

		foreach ($all_batch_ids as $batch_ids) {
			$batch = $this->getContainer()->getEm()->getRepository($entity)->getByIds($batch_ids);
			if ($batch) {
				$this->getContainer()->getSearchAdapter()->updateObjectsInIndex($batch);
			}

			$this->getContainer()->getEm()->clear();

			$output->write('.');
		}
		$output->writeln('');

		return 0;
	}
}
