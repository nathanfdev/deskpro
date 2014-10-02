<?php

/* * ************************************************************************\
  | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
  | a British company located in London, England.                            |
  |                                                                          |
  | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
  |                                                                          |
  | The license agreement under which this software is released              |
  | can be found at https://www.deskpro.com/eula/                            |
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
  \************************************************************************* */

/**
 * @package Importer
 */

namespace Application\ImportBundle\ArrayParser;

use Application\ImportBundle\Value\DownloadValue;
use Application\ImportBundle\Value\AttachmentValue;
use Orb\Util\Strings;

class DownloadArrayParser implements ArrayParserInterface
{

	/**
	 * @param array $data
	 * @return PersonValue
	 */
	public function parseArray(array $data)
	{
		$data = ArrayParserUtils::cleanArray($data);

		$value = new DownloadValue();

		ArrayParserUtils::copyValueMapping(array(
			'oid'			=> 'raw',
			'person'		=> 'string',
			'language'		=> 'string',
			'num_downloads'		=> 'int',
			'slug'			=> 'string',
			'title'			=> 'string',
			'content'		=> 'string',
			'view_count'		=> 'int',
			'total_rating'		=> 'int',
			'num_comments'		=> 'int',
			'num_ratings'		=> 'int',
			'status'		=> 'string',
			'date_created'		=> 'date',
			'date_published'	=> 'date',
			'category'		=> 'string',
			'labels'		=> 'array',
		), $data, $value);

		if ($value->title && !$value->slug) {
			$value->slug = Strings::slugifyTitle($value->title);
		}
		
		if (isset($data['attachment']) && !empty($data['attachment'])) {
			$attach_data = $data['attachment'];
			
			$attach_data = ArrayParserUtils::cleanArray($attach_data);
			
			$attach_value = new AttachmentValue();

			ArrayParserUtils::copyValueMapping(array(
				'oid'         => 'raw',
				'blob_data'    => 'raw',
				'blob_url'     => 'string',
				'blob_path'    => 'string',
				'file_name'     => 'string',
				'content_type' => 'string',
				'is_inline'    => 'bool'
			), $attach_data, $attach_value);

			$value->attachment = $attach_value;
		}

		return $value;
	}

}
