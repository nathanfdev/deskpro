<?php

namespace Application\DeskPRO\Controller;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Util;

class BlobController extends AbstractController
{
	public function showBlobAction($blob_auth_id)
	{
		if (strpos($blob_auth_id, '-') === false) {
			return $this->createResponse('');
		}

		list($blob_id, $blob_auth) = explode('-', $blob_auth_id, 2);

		$blob = App::getEntityRepository('DeskPRO:Blob')->find($blob_id);

		if (!$blob OR $blob['authcode'] != $blob_auth) {
			return $this->createResponse('');
		}

		return $this->getDownloadResponse($blob, array(
			'size' => $this->in->getString('s')
		));
	}

	protected function getDownloadResponse($blob, array $options = array())
	{
		$response = $this->container->get('response');
		$response->headers->set('Content-Type', $blob['content_type'] . '; filename=' . $blob['filename']);
		$response->headers->set('Content-Length', $blob['filesize']);

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

		$desc = App::getApi('filestorage')->getFileDescriptor($blob['id']);

		$file = $desc->get();
		unset($desc);

		if (!empty($options['size']) AND $blob->isImage()) {
			$im = new \Imagick();
			$im->readImageBlob($file, $blob['filename']);
			$im->resizeImage($options['size'], $options['size'], \Imagick::FILTER_LANCZOS, true);

			$file = $im->getImageBlob();
			$size = strlen($file);

			$response->headers->set('Content-Length', $size);
		}

		$response->setContent($file);

		return $response;
	}

	public function personPictureAction($person_id, $size)
	{
		$person = App::getEntityRepository('DeskPRO:Person')->find($person_id);

		if ($person['picture_blob']) {
			$response = return $this->getDownloadResponse($person['picture_blob'], array(
				'size' => $size
			));
		} else {
			$gravatar_url = $person->getGravatarUrl($size, true);
			$response = $this->response;
			$response->setRedirect($gravatar_url);
		}

		$response->setExpires(date_create("+1 days"));
		$response->setMaxAge(86400);
		$response->setSharedMaxAge(86400);
		$response->setPublic();

		return $response;
	}
}
