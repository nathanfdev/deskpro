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
			'size' => $this->in->getString('s'),
			'size-fit' => $this->in->getBool('size-fit')
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

			if (empty($options['size-fit']) OR (!empty($options['size-fit']) AND $options['size-fit'])) {
				$im->scaleImage($options['size'], $options['size'], true);
			} else {
				$im->scaleImage($options['size'], $options['size'], false);
			}

			$file = $im->getImageBlob();
			$size = strlen($file);

			$response->headers->set('Content-Length', $size);
		}

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
		$person = App::getEntityRepository('DeskPRO:Person')->find($person_id);

		if ($person->hasPicture()) {
			if ($person['picture_blob']) {
				$response = $this->getDownloadResponse($person['picture_blob'], array(
					'size' => $size
				));
			} elseif ($person['gravatar_url']) {
				$gravatar_url = $person['gravatar_url'];
				if ($this->request->isSecure()) {
					$gravatar_url = preg_replace('#^http:#', 'https:', $gravatar_url);
				}
				$gravatar_url .= '&s=' . $size;
				$response = $this->response;
				$response->setRedirect($gravatar_url);
			}
		} else {
			$response = $this->_serveDefaultPicture($size);
		}

		$response->setExpires(date_create("+1 days"));
		$response->setMaxAge(86400);
		$response->setSharedMaxAge(86400);
		$response->setPublic();

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
				return $this->_serveDefaultPicture($this->in->getUint('s'));
				break;
		}

		throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("Unknown name");
	}

	protected function _serveDefaultPicture($size = 80)
	{
		if (!$size) {
			$size = 80;
		}

		$response = $this->container->get('response');

		$response->setExpires(date_create("+2 years"));
		$response->setMaxAge(31556926);
		$response->setSharedMaxAge(31556926);
		$response->setPublic();

		$im = new \Imagick();
		$im->readImage(DP_ROOT . '/src/Application/DeskPRO/Resources/assets/picture-default.jpeg');
		$im->resizeImage($size, $size, \Imagick::FILTER_LANCZOS, true);

		$file = $im->getImageBlob();
		$size = strlen($file);

		$response->headers->set('Content-Length', $size);
		$response->setContent($file);

		return $response;
	}
}
