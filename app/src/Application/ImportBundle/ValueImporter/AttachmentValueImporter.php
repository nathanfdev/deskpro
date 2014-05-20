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

namespace Application\ImportBundle\ValueImporter;

use Application\ImportBundle\Value\AttachmentValue;
use Application\ImportBundle\Exception\BadDataException;
use Application\ImportBundle\Exception\DuplicateValueException;
use Orb\Validator\StringEmail;

class AttachmentValueImporter extends AbstractValueImporter
{
	/**
	 * 
	 * @param \Application\ImportBundle\Value\FeedbackValue $aval
	 * @return boolean
	 * @throws \InvalidArgumentException
	 * @throws BadDataException
	 * @throws DuplicateValueException
	 */
	public function importValue($aval)
	{
		if (!($aval instanceof AttachmentValue)) {
			throw new \InvalidArgumentException("This importer can only import Attachments");
		}
		
		$log_id = "Attachment :: " . $aval->oid . " ";
		
		if (!$aval->blob_data &&
			!$aval->blob_path &&
			!$aval->blob_url) {
			throw new BadDataException(sprintf("[%s] Invalid Attachment: The attachement must have one of 'blob_data', 'blob_path' or 'blob_url'", $log_id));
		}
		
		if ($aval->blob_data) {
			$blob_data = base64_decode($aval->blob_data);
		} elseif ($aval->blob_path) {
			if (!is_readable($aval->blob_path)) {
				throw new BadDataException(sprintf('[%s] Invalid blob path %s', $log_id, $aval->blob_path));
			}
			
			$blob_data = file_get_contents($aval->blob_path);
		} elseif ($aval->blob_url) {
			if (!is_readable($aval->blob_url)) {
				throw new BadDataException(sprintf('[%s] Invalid blob url %s', $log_id, $aval->blob_url));
			}
			$blob_data = file_get_contents($aval->blob_url);
		}
			
		$blobStarage = $this->getContainer()->getBlobStorage();

		$blob = $blobStarage->createBlobRecordFromString($blob_data, $aval->filename, $aval->content_type);
		
		return $blob;
	}
}
