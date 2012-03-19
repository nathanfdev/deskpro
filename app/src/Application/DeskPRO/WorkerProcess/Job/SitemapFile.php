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
 * @subpackage WorkerProcess
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\Mail\QueueProcessor\Database as DatabaseQueueProcessor;

use Application\DeskPRO\App;
use Application\DeskPRO\Log\Logger;
use Application\DeskPRO\Mail\Transport\DelegatingTransport;

/**
 * Updates the sitemap file
 */
class SitemapFile extends AbstractJob
{
	const DEFAULT_INTERVAL = 604800; // 7 days

	public function run()
	{
		$old_sitemap_file = App::getSetting('core.sitemap_blob_id');

		if ($old_sitemap_file) {
			$old_desc = App::getContainer()->getFilestorage()->getFileDescriptor($old_sitemap_file);
			if ($old_desc) {
				$old_desc->delete();
			}
		}

		$gen = new \Application\DeskPRO\Portal\SitemapGenerator(App::getSetting('core.deskpro_url'), App::getOrm(), App::getRouter());
		$file = $gen->getXml();

		$new_desc = App::getContainer()->getFilestorage()->createRandomPath();
		$new_desc->write($file, array(
			'filename' => 'sitemap.xml',
			'content_type' => 'text/xml',
			'sys_name' => 'sitemap_xml',
		));
	}
}
