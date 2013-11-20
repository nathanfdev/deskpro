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

namespace Application\ApiBundle\Controller;

class ServerFileUploadsController extends AbstractController
{
	####################################################################################################################
	# get
	####################################################################################################################

	public function getAction()
	{
		/**
		 * @var \Application\DeskPRO\ServerFileUploads\ServerFileUploads $server_file_uploads
		 */

		$server_file_uploads = $this->container->getSystemService('server_file_uploads');

		$returnedData['php_vars']                  = $server_file_uploads->getPhpVars();
		$returnedData['effective_max_upload_size'] = $server_file_uploads->getEffectiveMaxUploadSize();
		$returnedData['url_to_learn_php_ini']      = $server_file_uploads->getUrlToLearnPhpIni();
		$returnedData['php_ini_path']              = $server_file_uploads->getPhpIniPath();
		$returnedData['restrictions']              = $server_file_uploads->getRestrictions();
		$returnedData['file_uploader_url']         = $server_file_uploads->getFileUploaderUrl();
		$returnedData['using_file_system']         = $server_file_uploads->isUsingFileSystem();
		$returnedData['file_storage_path']         = $server_file_uploads->getFileStoragePath();
		$returnedData['moving_files']              = $server_file_uploads->getMovingFiles();

		return $this->createApiResponse(
			array(
				 'server_file_uploads' => $returnedData
			)
		);
	}

	####################################################################################################################
	# test upload
	####################################################################################################################

	public function testUploadAction()
	{
		/**
		 * @var \Application\DeskPRO\ServerFileUploads\ServerFileUploads $server_file_uploads
		 */

		$server_file_uploads = $this->container->getSystemService('server_file_uploads');

		$file   = $this->request->files->get('file');

		return $this->createApiResponse(
			$server_file_uploads->getUploadResults($file)
		);
	}

	public function switchStorageAction()
	{
		/**
		 * @var \Application\DeskPRO\ServerFileUploads\ServerFileUploads $server_file_uploads
		 */

		$server_file_uploads = $this->container->getSystemService('server_file_uploads');

		$server_file_uploads->switchStorage();

		return $this->createSuccessResponse();
	}
}