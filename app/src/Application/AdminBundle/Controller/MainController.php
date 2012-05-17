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

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;

class MainController extends AbstractController
{
    public function indexAction()
	{
		return $this->render('AdminBundle:Main:index.html.twig');
	}

	public function dashVersionInfoAction()
	{
		$version_info = \Application\DeskPRO\Service\LicenseService::compareVersion();

		return $this->render('AdminBundle:Main:part-version-info.html.twig', array(
			'version_info' => $version_info
		));
	}

	public function acceptTempUploadAction()
	{
		$file = $this->request->files->get('file-upload');
		$desc = App::getApi('filestorage')->createRandomPath();

		$desc->write(file_get_contents($file->getRealPath()), array(
			'content_type' => $file->getClientMimeType(),
			'filename' => $file->getClientOriginalName()
		));

		$blob_id = $desc->getPath();
		$blob = $this->em->getRepository('DeskPRO:Blob')->find($blob_id);

		if ($this->in->getString('attach_to_object')) {
			switch ($this->in->getString('attach_to_object')) {
				case 'article':
					$article = $this->em->find('DeskPRO:Article', $this->in->getUint('object_id'));

					$attach = new \Application\DeskPRO\Entity\ArticleAttachment();
					$attach['blob'] = $blob;
					$attach['person'] = $this->person;

					$article->addAttachment($attach);

					$this->em->persist($article);
					$this->em->flush();

					break;
			}
		}

		return $this->createJsonResponse(array(array(
			'blob_id' => $blob['id'],
			'blob_auth' => $blob->authcode,
			'blob_auth_id' => $blob->id . '-' . $blob->authcode,
			'download_url' => $blob->getDownloadUrl(true),
			'filename' => $blob['filename'],
			'filesize_readable' => $blob->getReadableFilesize()
		)));
	}

	public function skipSetupStepAction()
	{
		$this->setup_guide->skipNextTask();
		return $this->redirectRoute('admin');
	}

	public function sessionPingAction()
	{
		return $this->createJsonResponse(array('okay' => 1));
	}
}
