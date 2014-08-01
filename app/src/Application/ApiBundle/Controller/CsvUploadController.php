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

use Application\ApiBundle\PermissionStrategy\AdminManagePermission;

class CsvUploadController extends AbstractController implements ProtectedControllerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getPermissionStrategy()
	{
		return new AdminManagePermission();
	}

	####################################################################################################################
	# upload
	####################################################################################################################

	public function uploadAction()
	{
		$file = $this->request->files->get('file');

		/**
		 * @var \Application\DeskPRO\CsvUpload\CsvUpload $csv_upload
		 */

		$csv_upload = $this->container->getSystemService('csv_upload');
		$options = $this->in->getArrayValue('options');

		$result = $csv_upload->upload($file, $options);

		return $this->createApiResponse($result);
	}

	####################################################################################################################
	# import
	####################################################################################################################

	public function importAction()
	{
		$field_maps    = $this->in->getCleanValueArray('field_maps', 'raw', 'uint');
		$user_filename = $this->in->getString('user_filename');
		$skip_first    = $this->in->getBool('skip_first');
		$welcome_email = $this->in->getBool('welcome_email');
		$filename      = $this->in->getUint('filename');
		$options       = $this->in->getArrayValue('options');

		/**
		 * @var \Application\DeskPRO\CsvUpload\CsvUpload $csv_upload
		 */

		$csv_upload = $this->container->getSystemService('csv_upload');

		$result = $csv_upload->startImportTask($field_maps, $filename, $user_filename, $skip_first, $welcome_email, $options);

		return $this->createApiResponse($result);
	}

	####################################################################################################################
	# status
	####################################################################################################################

	public function statusAction()
	{
		/**
		 * @var \Application\DeskPRO\CsvUpload\CsvUpload $csv_upload
		 */

		$csv_upload = $this->container->getSystemService('csv_upload');

		$result = $csv_upload->returnStatusOfImport();

		return $this->createApiResponse($result);
	}
}