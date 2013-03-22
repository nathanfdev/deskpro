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
 * DeskPRO
 *
 * @package DeskPRO
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\BlobStorage\StorageAdapter\AmazonS3Storage;
use Application\DeskPRO\BlobStorage\StorageAdapter\FilesystemStorage;
use Application\DeskPRO\BlobStorage\StorageAdapter\DatabaseStorage;
use Aws\S3\S3Client;
use Orb\Log\Logger;

class BlobStorageService
{
	public static function create(DeskproContainer $container)
	{
		#------------------------------
		# Create a logger
		#------------------------------

		$logger = new Logger();

		if (!dp_get_config('enable_blobstorage_log')) {
			$logger->addFilter(new \Orb\Log\Filter\PriorityFilter(Logger::WARN));
		}

		$wr = new \Orb\Log\Writer\Stream($container->getLogDir() . DIRECTORY_SEPARATOR . 'blob_storage.log');
		$logger->addWriter($wr);

		#------------------------------
		# Create the storage and adapters
		#------------------------------

		$bs = new DeskproBlobStorage($container->getEm());
		$bs->setLogger($logger);

		if ($container->getSetting('core.filestorage_method') == 's3') {
			$client = S3Client::factory(array(
				'key'    => $container->getSetting('core.filestorage_s3_key'),
				'secret' => $container->getSetting('core.filestorage_s3_secret')
			));
			$adapter = new AmazonS3Storage(array(
				's3_client' => $client,
				'bucket'    => $container->getSetting('core.filestorage_s3_bucket'),
				'base_path' => $container->getSetting('core.filestorage_s3_basepath')
			));
			$adapter->setLogger($logger);

			$bs->addAdapter('s3', $adapter);
		} elseif ($container->getSetting('core.filestorage_method') == 'fs') {
			$adapter = new FilesystemStorage(array(
				'base_path' => $container->getBlobDir(),
			));
			$adapter->setLogger($logger);

			$bs->addAdapter('fs', $adapter);
		}

		// Always fallback on DB
		$adapter = new DatabaseStorage(array(
			'db'                   => $container->getDb(),
			'table'                => 'blobs_storage',
			'field_name.data'      => 'data',
			'field_name.path'      => 'blob_id',
			'field_name.order'     => 'id',
			'metadata_id_property' => 'blob_id'
		));
		$adapter->setLogger($logger);
		$bs->addAdapter('db', $adapter);

		return $bs;
	}
}
