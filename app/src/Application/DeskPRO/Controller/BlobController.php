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
	public function showBlobAction($blob_auth_id)
	{
		if (strpos($blob_auth_id, '-') === false) {
			return $this->createResponse('');
		}

		list($blob_id, $blob_auth) = explode('-', $blob_auth_id, 2);

		$blob = $this->em->getRepository('DeskPRO:Blob')->find($blob_id);

		if (!$blob OR $blob['authcode'] != $blob_auth) {
			return $this->createResponse('');
		}

		return $this->getDownloadResponse($blob, array(
			'size' => $this->in->getString('s'),
			'size-fit' => $this->in->getBool('size-fit')
		));
	}

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
				$desc = App::getApi('filestorage')->getFileDescriptor($cached_blob['id']);
				$file = $desc->get();
				unset($desc);
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
	 * This is a special action used in some cases where we need a similar URL to serve
	 * all pictures. For example, in a Javascript interface we dont want to do an ajax
	 * call just to get a URL; easier to just render or redirect right in one img request.
	 */
	public function personPictureAction($person_id, $size)
	{
		$person = $this->em->getRepository('DeskPRO:Person')->find($person_id);

		if ($person->hasPicture()) {
			if ($person['picture_blob']) {
				$response = $this->getDownloadResponse($person['picture_blob'], array(
					'size' => $size,
					'cache' => true,
					'cache_date_cleanup' => new \DateTime('+2 weeks')
				));
			} elseif ($person['gravatar_url']) {
				$gravatar_url = $person['gravatar_url'];
				if ($this->request->isSecure()) {
					$gravatar_url = preg_replace('#^http:#', 'https:', $gravatar_url);
				}
				$gravatar_url .= '&s=' . $size;
				$response = new \Symfony\Component\HttpFoundation\RedirectResponse($gravatar_url);
				$response->setExpires(date_create("+2 days"));
				$response->setMaxAge(86400);
				$response->setSharedMaxAge(86400);
			}
		} else {
			$response = $this->_serveDefaultPicture($size);
		}

		$response->setPublic();

		return $response;
	}

	public function orgPictureAction($org_id)
	{
		$size = $this->in->getUint('size');
		if (!Numbers::inRange($size, 5, 200)) {
			$size = 80;
		}

		$org = $this->em->getRepository('DeskPRO:Organization')->find($org_id);

		if ($person->picture_blob) {
			$response = $this->getDownloadResponse($org, array(
				'size' => $size,
				'cache' => true,
				'cache_date_cleanup' => new \DateTime('+2 weeks')
			));
		} else {
			$response = $this->defaultOrgPictureAction($size);
		}

		$response->setPublic();

		return $response;
	}

	public function defaultOrgPictureAction()
	{
		$size = $this->in->getUint('size');
		if (!Numbers::inRange($size, 5, 200)) {
			$size = 80;
		}

		$img_path = DP_ROOT . '/src/Application/DeskPRO/Resources/assets/orgpicture-default.jpeg';
		$sys_name = 'dp.orgpicture-default';

		if ($size == 200) {
			$file = file_get_contents($img_path);
		} else {

			$name = $sys_name . '-' . $size;
			$cached_blob = $this->em->getRepository('DeskPRO:Blob')->getSystemBlob($name);

			if (!$cached_blob) {
				$desc = App::getApi('filestorage')->createRandomPath();

				$image = $this->container->getImagine()->open($img_path);
				$image->resize(new \Imagine\Image\Box($size, $size));

				$file = $image->get('jpeg');

				$desc->write($file, array(
					'content_type' => 'image/jpeg',
					'filename' => basename($img_path),
					'sys_name' => $name,
				));
			} else {
				$desc = App::getApi('filestorage')->getFileDescriptor($cached_blob['id']);
				$file = $desc->get();
				unset($desc);
			}
		}

		$size = strlen($file);

		$response = $this->container->get('response');
		$response->headers->set('Content-Type', 'image/jpeg; filename=' . basename($img_path));
		$response->headers->set('Content-Disposition', 'inline; filename=' . basename($img_path));
		$response->headers->set('Content-Length', $size);
		$response->setLastModified(date_create("-6 months"));
		$response->setExpires(date_create("+6 months"));
		$response->setMaxAge(31556926);
		$response->setSharedMaxAge(31556926);
		$response->setPublic();
		$response->setContent($file);

		return $response;
	}



	/**
	 * Static files
	 */
	public function getStaticFileAction($name)
	{
		$response = $this->container->get('response');

		$response->setExpires(date_create("+2 years"));
		$response->setMaxAge(31556926);
		$response->setSharedMaxAge(31556926);
		$response->setPublic();

		switch ($name) {
			case 'pix':
				$gif = base64_decode(
					'R0lGODlhAQABALMAAAAAAIAAAACAA'.
					'ICAAAAAgIAAgACAgMDAwICAgP8AAA'.
					'D/AP//AAAA//8A/wD//wBiZCH5BAE'.
					'AAA8ALAAAAAABAAEAAAQC8EUAOw=='
				);
				$response->headers->set('Content-Type', 'image/gif; filename=pix.gif');
				$response->headers->set('Content-Length', strlen($gif));
				$response->setContent($gif);

				return $response;
				break;

			case 'default_picture':
				return $this->_serveDefaultPicture($this->in->getUint('s'), $this->in->getBool('is_agent'));
				break;
		}

		throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Unknown name");
	}

	protected function _serveDefaultPicture($size = 80, $is_agent = false)
	{
		if (!$size) {
			$size = 80;
		}

		if ($is_agent) {
			$img_path = DP_ROOT . '/src/Application/DeskPRO/Resources/assets/picture-default-agent.jpeg';
			$sys_name = 'dp.picture-default-agent';
		} else {
			$img_path = DP_ROOT . '/src/Application/DeskPRO/Resources/assets/picture-default.jpeg';
			$sys_name = 'dp.picture-default';
		}

		if ($size == 200) {
			$file = file_get_contents($img_path);
		} else {

			$name = $sys_name . '-' . $size;
			$cached_blob = $this->em->getRepository('DeskPRO:Blob')->getSystemBlob($name);

			if (!$cached_blob) {
				$desc = App::getApi('filestorage')->createRandomPath();

				$image = $this->container->getImagine()->open($img_path);
				$image->resize(new \Imagine\Image\Box($size, $size));

				$file = $image->get('jpeg');

				$desc->write($file, array(
					'content_type' => 'image/jpeg',
					'filename' => basename($img_path),
					'sys_name' => $name,
				));
			} else {
				$desc = App::getApi('filestorage')->getFileDescriptor($cached_blob['id']);
				$file = $desc->get();
				unset($desc);
			}
		}

		$size = strlen($file);

		$response = $this->container->get('response');
		$response->headers->set('Content-Type', 'image/jpeg; filename=' . basename($img_path));
		$response->headers->set('Content-Disposition', 'inline; filename=' . basename($img_path));
		$response->headers->set('Content-Length', $size);
		$response->setLastModified(date_create("-6 months"));
		$response->setExpires(date_create("+6 months"));
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
