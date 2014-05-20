<?php

/* * ************************************************************************\
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
  \************************************************************************* */

/**
 * @package Importer
 */

namespace Application\ImportBundle\ArrayParser;

use Application\ImportBundle\Value\KbValue;
use Orb\Util\Strings;

class KbArrayParser implements ArrayParserInterface
{

	/**
	 * @param array $data
	 * @return PersonValue
	 */
	public function parseArray(array $data)
	{
		$data = ArrayParserUtils::cleanArray($data);

		$value = new KbValue();

		ArrayParserUtils::copyValueMapping(array(
			'oid'		=> 'raw',
			'person'	=> 'string',
			'language'	=> 'string',
			'date_end'	=> 'date',
			'end_action'	=> 'string',
			'slug'		=> 'string',
			'title'		=> 'string',
			'content'	=> 'string',
			'total_rating'	=> 'int',
			'num_comments'	=> 'int',
			'num_ratings'	=> 'int',
			'status'	=> 'string',
			'date_created'	=> 'date',
			'date_published'	=> 'date',
			'categories'	=> 'array',
			'labels'	=> 'array',
		), $data, $value);

		if ($value->title && !$value->slug) {
			$value->slug = Strings::slugifyTitle($value->title);
		}

		return $value;
	}

}
