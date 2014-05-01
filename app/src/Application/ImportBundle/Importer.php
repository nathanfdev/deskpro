<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * @package Importer
 */

namespace Application\ImportBundle;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\ImportBundle\Exception\BadDataException;
use Application\ImportBundle\Exception\DuplicateValueException;
use Application\ImportBundle\Exception\MissingMappingExceptionException;
use Application\ImportBundle\Exception\MultipleMappingException;
use Application\ImportBundle\RecordMapper\CommonRecordMapper;
use Application\ImportBundle\RecordMapper\RecordMapperRegistry;
use Application\ImportBundle\RecordMapper\TicketDepartmentRecordMapper;
use Application\ImportBundle\RecordMapper\TicketStatusRecordMapper;
use Application\ImportBundle\RecordMapper\PersonRecordMapper;
use Application\ImportBundle\ArrayParser\PersonArrayParser;
use Application\ImportBundle\ArrayParser\TicketArrayParser;
use Application\ImportBundle\ArrayParser\KbArrayParser;
use Application\ImportBundle\ArrayParser\NewsArrayParser;
use Application\ImportBundle\ArrayParser\FeedbackArrayParser;
use Application\ImportBundle\ArrayParser\DownloadArrayParser;
use Application\ImportBundle\ValueImporter\AbstractValueImporter;
use Application\ImportBundle\ValueImporter\PersonValueImporter;
use Application\ImportBundle\ValueImporter\TicketValueImporter;
use Application\ImportBundle\ValueImporter\KbValueImporter;
use Application\ImportBundle\ValueImporter\NewsValueImporter;
use Application\ImportBundle\ValueImporter\FeedbackValueImporter;
use Application\ImportBundle\ValueImporter\DownloadValueImporter;
use DeskPRO\Kernel\KernelErrorHandler;
use Application\DeskPRO\DBAL\Connection;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Orb\Util\Util;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

class Importer
{
	const EVENT_RESET_DONE       = 'reset_done_marker';
	const EVENT_PRE_IMPORT_READ  = 'pre_import_read';
	const EVENT_PRE_IMPORT       = 'pre_import';
	const EVENT_POST_IMPORT      = 'post_import';
	const EVENT_PRE_STEP         = 'pre_step';
	const EVENT_POST_STEP        = 'post_step';

	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	private $db;

	/**
	 * @var ImporterConfig
	 */
	private $config;

	/**
	 * @var \Application\ImportBundle\RecordMapper\RecordMapperRegistry
	 */
	private $mappers;

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	private $logger;

	/**
	 * @var ImporterStatusCallback
	 */
	private $status_callback;
	
	/**
	 * @var DeskproContainer
	 */
	private $container;


	/**
	 * @param Connection      $db
	 * @param ImporterConfig  $config
	 * @param LoggerInterface $logger
	 */
	public function __construct(DeskproContainer $container, ImporterConfig $config, LoggerInterface $logger = null)
	{
		$this->container = $container;
		
		$this->db = $container->getDb();

		$this->config = $config;

		$this->mappers = new RecordMapperRegistry();
		$this->mappers['person']		= new PersonRecordMapper($this->db);
		$this->mappers['ticket_department']	= new TicketDepartmentRecordMapper($this->db);
		$this->mappers['ticket_category']	= new CommonRecordMapper($this->db, 'ticket_categories', 'title');
		$this->mappers['ticket_workflow']	= new CommonRecordMapper($this->db, 'ticket_workflows', 'title');
		$this->mappers['ticket_priority']	= new CommonRecordMapper($this->db, 'ticket_priorities', 'title');
		$this->mappers['ticket_status']		= new TicketStatusRecordMapper();
		$this->mappers['department']		= new CommonRecordMapper($this->db, 'departments', 'title');
		$this->mappers['product']		= new CommonRecordMapper($this->db, 'products', 'title');
		$this->mappers['usergroup']		= new CommonRecordMapper($this->db, 'usergroups', 'title');
		$this->mappers['organization']		= new CommonRecordMapper($this->db, 'organizations', 'name');
		$this->mappers['language']		= new CommonRecordMapper($this->db, 'languages', 'title');
		$this->mappers['article_category']	= new CommonRecordMapper($this->db, 'article_categories', 'title');
		$this->mappers['article']		= new CommonRecordMapper($this->db, 'articles', 'title');
		$this->mappers['news_category']		= new CommonRecordMapper($this->db, 'news_categories', 'title');
		$this->mappers['news']			= new CommonRecordMapper($this->db, 'news', 'title');
		$this->mappers['feedback_category']	= new CommonRecordMapper($this->db, 'feedback_categories', 'title');
		$this->mappers['feedback']		= new CommonRecordMapper($this->db, 'feedback', 'title');
		$this->mappers['download_category']	= new CommonRecordMapper($this->db, 'download_categories', 'title');
		$this->mappers['download']		= new CommonRecordMapper($this->db, 'download', 'title');

		if (!$logger) {
			$logger = new Logger('importer');
		}

		if ($config->log_path) {
			$logger->pushHandler(new StreamHandler($config->log_path . '.full.log', Logger::INFO));
			$logger->pushHandler(new StreamHandler($config->log_path . '.notice.log', Logger::NOTICE));
			$logger->pushHandler(new StreamHandler($config->log_path . '.error.log', Logger::ERROR));
		}

		$this->logger = $logger;
	}


	/**
	 * @param ImporterStatusCallback $callback
	 */
	public function setStatusCallback(ImporterStatusCallback $callback)
	{
		$this->status_callback = $callback;
	}


	/**
	 * Reset all done markers.
	 */
	public function resetDoneMarkers()
	{
		foreach ($this->getDirectoryIterator($this->config->data_path, false) as $file) {
			if (file_exists($file->getPath() . '.done')) {
				unlink(@file_exists($file->getPath() . '.done'));
				if ($this->status_callback) $this->status_callback->postResetDoneMarker($this, $file);
			}
		}
	}


	public function processImports()
	{
		$this->processDirectory('people', new PersonValueImporter(
			$this->config->mode,
			$this->container,
			$this->logger,
			$this->mappers
		));
		
		$this->processDirectory('tickets', new TicketValueImporter(
			$this->config->mode,
			$this->container,
			$this->logger,
			$this->mappers
		));
		
		$this->processDirectory('articles', new KbValueImporter(
			$this->config->mode,
			$this->container,
			$this->logger,
			$this->mappers
		));
		
		$this->processDirectory('news', new NewsValueImporter(
			$this->config->mode,
			$this->container,
			$this->logger,
			$this->mappers
		));
		
		$this->processDirectory('feedback', new FeedbackValueImporter(
			$this->config->mode,
			$this->container,
			$this->logger,
			$this->mappers
		));
		
		$this->processDirectory('downloads', new DownloadValueImporter(
			$this->config->mode,
			$this->container,
			$this->logger,
			$this->mappers
		));
	}


	/**
	 * @param string $dir
	 * @param AbstractValueImporter $value_importer
	 * @throws \Exception
	 */
	private function processDirectory($dir, AbstractValueImporter $value_importer)
	{
		if ($this->status_callback) $this->status_callback->preStep($this, $value_importer, $dir);
		$step_start = microtime(true);

		$it = $this->getDirectoryIterator($dir, true);

		$count = 0;
		foreach ($it as $file) {
			$count++;
			/** @var \SplFileInfo $file */
			$json = @file_get_contents($file->getRealPath());

			if ($this->status_callback) $this->status_callback->preImportValueRead($this, $value_importer, $file, $count);

			if (!$json) {
				$this->getLogger()
					->warning("File is not readable or empty: " . $file->getRealPath());
				continue;
			}

			$data = json_decode($json, true);
			unset($json);
			if (!$data) {
				$this->getLogger()
					->warning("Invalid JSON data file: " . $file->getRealPath());
				continue;
			}

			try {
				$start = microtime(true);

				if ($this->status_callback) $this->status_callback->preImportValue($this, $value_importer, $file, $count, $data);

				//TODO clean this up
				switch (Util::getBaseClassname($value_importer)) {
					case 'PersonValueImporter':
						$parser = new PersonArrayParser();
						$value = $parser->parseArray($data);
						break;
					case 'TicketValueImporter':
						$parser = new TicketArrayParser();
						$value = $parser->parseArray($data);
						break;
					case 'KbValueImporter':
						$parser = new KbArrayParser();
						$value = $parser->parseArray($data);
						break;
					case 'NewsValueImporter':
						$parser = new NewsArrayParser();
						$value = $parser->parseArray($data);
						break;
					case 'FeedbackValueImporter':
						$parser = new FeedbackArrayParser();
						$value = $parser->parseArray($data);
						break;
					case 'DownloadValueImporter':
						$parser = new DownloadArrayParser();
						$value = $parser->parseArray($data);
						break;
				}

				$value_importer->importValue($value);
				if ($this->status_callback) $this->status_callback->postImportValue($this, $value_importer, $file, $count, $data, microtime(true) - $start);
			} catch (BadDataException $ex) {
				$this->getLogger()
					->warning(sprintf("Invalid or missing data in file (@%s) -- %s", $file->getRealPath(), $ex->getMessage()));
			} catch (DuplicateValueException $ex) {
				$this->getLogger()
					->warning(sprintf("Duplicate value detected (@%s) -- %s", $file->getRealPath(), $ex->getMessage()));
			} catch (MissingMappingExceptionException $ex) {
				$this->getLogger()
					->warning(sprintf("Invalid mapping detected (@%s) -- %s", $file->getRealPath(), $ex->getMessage()));
			} catch (MultipleMappingException $ex) {
				$this->getLogger()
					->warning(sprintf("Multiple candidate mappings detected (@%s) -- %s", $file->getRealPath(), $ex->getMessage()));
			} catch (\Exception $ex) {
				$this->getLogger()
					->critical(sprintf("Unhandled exception while processing (@%s) -- %s\n%s", $file->getRealPath(), $ex->getMessage(), KernelErrorHandler::formatBacktrace($ex->getTrace())));
				throw $ex;
			}
		}

		if ($this->status_callback) $this->status_callback->postStep($this, $value_importer, $dir, $count, microtime(true) - $step_start);
	}


	/**
	 * @param string $dir
	 * @param bool $exclude_done
	 * @return \RecursiveIteratorIterator
	 */
	public function getDirectoryIterator($dir, $exclude_done)
	{
		$iterator = new RecursiveDirectoryIterator($this->config->data_path . '/' . $dir, RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::CURRENT_AS_FILEINFO);

		$filter = new DirectoryIteratorFilter($iterator);
		if ($exclude_done) {
			$filter->excludeDone();
		}

		$it = new \RecursiveIteratorIterator($filter, \RecursiveIteratorIterator::SELF_FIRST | \RecursiveIteratorIterator::LEAVES_ONLY);
		return $it;
	}


	/**
	 * @return LoggerInterface
	 */
	public function getLogger()
	{
		return $this->logger;
	}
}