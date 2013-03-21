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
*/

namespace Application\DeskPRO\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Util;
use Orb\Util\Numbers;

class BlobController extends AbstractController
{
	protected function getDownloadResponse($blob, array $options = array())
	{
		$response = $this->container->get('response');

		if (!empty($options['size']) AND $blob->isImage()) {

			$cached_blob = null;
			$name = 'blob-' . $blob['id'] . '-' . $options['size'];

			if (isset($options['cache']) && $options['cache']) {

				//cache_date_cleanup
				$cached_blob = $this->em->getRepository('DeskPRO:Blob')->getSystemBlob($name);
			}

			if ($cached_blob) {
				$file = App::getContainer()->getBlobStorage()->copyBlobRecordToString($cached_blob);
			} else {
				$desc = App::getApi('filestorage')->getFileDescriptor($blob['id']);
				$file = $desc->get();
				unset($desc);

				$image = $this->container->getImagine()->load($file);
				$image->resize(new \Imagine\Image\Box($options['size'], $options['size']));
				$file = $image->get($blob->getImageType());

				$desc = App::getApi('filestorage')->createRandomPath();
				$desc->write($file, array(
					'content_type' => $blob->content_type,
					'filename' => $blob->filename,
					'sys_name' => $name,
					'date_cleanup' => isset($options['cache_date_cleanup']) ? $options['cache_date_cleanup'] : null,
					'original_blob_id' => $blob->id,
				));
			}
		} else {
			$desc = App::getApi('filestorage')->getFileDescriptor($blob['id']);
			$file = $desc->get();
			unset($desc);
		}

		$response->headers->set('Content-Type', $blob['content_type'] . '; filename=' . $blob['filename']);
		$response->headers->set('Content-Length', strlen($file));

		if ($blob->isImage()) {
			$response->headers->set('Content-Disposition', 'inline; filename=' . $blob['filename']);
		} else {
			$response->headers->set('Content-Disposition', 'attachment; filename=' . $blob['filename']);
		}

		// Blobs are always the same. If there were such a thing as "edit", its delete+new blob
		// So its safe to set the hard cache options
		$response->setLastModified($blob['date_created']);
		$response->setExpires(date_create("+2 years"));
		$response->setMaxAge(31556926);
		$response->setSharedMaxAge(31556926);
		$response->setPublic();
		$response->setContent($file);

		return $response;
	}

	/**
	 * Favicon
	 */
	public function faviconAction()
	{
		$favicon_id = $this->container->getSetting('core.favicon_blob_id');
		$blob = null;
		if ($favicon_id) {
			$blob = $this->em->getRepository('DeskPRO:Blob')->find($favicon_id);
		}

		if ($blob) {
			$response = $this->getDownloadResponse($blob);
		} else {
			$file = file_get_contents(DP_ROOT . '/src/Application/DeskPRO/Resources/assets/favicon.ico');

			$response = $this->container->get('response');
			$response->headers->set('Content-Length', strlen($file));
			$response->setContent($file);
		}

		$response->headers->set('Content-Type', 'image/vnd.microsoft.icon; filename=favicon.ico');
		$response->headers->set('Content-Disposition', 'inline; filename=favicon.ico');
		$response->setExpires(date_create("+5 days"));
		$response->setMaxAge(432000);
		$response->setSharedMaxAge(432000);
		$response->setPublic();

		return $response;
	}
}
