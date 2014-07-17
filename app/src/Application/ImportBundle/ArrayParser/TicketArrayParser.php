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

namespace Application\ImportBundle\ArrayParser;

use Application\ImportBundle\Value\AttachmentValue;
use Application\ImportBundle\Value\TicketMessageValue;
use Application\ImportBundle\Value\TicketValue;
use Application\ImportBundle\Value\CustomDefValue;

class TicketArrayParser implements ArrayParserInterface
{
	/**
	 * @param array $data
	 * @return PersonValue
	 */
	public function parseArray(array $data)
	{
		$data = ArrayParserUtils::cleanArray($data);

		$value = new TicketValue();

		ArrayParserUtils::copyValueMapping(array(
			'oid'           => 'raw',
			'ref'           => 'string',
			'language'      => 'string',
			'department'    => 'string',
			'category'      => 'string',
			'priority'      => 'string',
			'workflow'      => 'string',
			'product'       => 'string',
			'person'        => 'string',
			'agent'         => 'string',
			'agent_team'    => 'string',
			'organization'  => 'string',
			'labels'        => 'array',
			'status'        => 'string',
			'is_hold'       => 'bool',
			'urgency'       => 'int',
			'date_created'  => 'date',
			'date_resolved' => 'date',
			'date_archived'   => 'date',
			'subject'       => 'string',
			'participants'  => 'array',
			'custom_fields' => 'array'
		), $data, $value);

		if (!empty($data['messages'])) {
			foreach ($data['messages'] as $message_data) {
				$message_data = ArrayParserUtils::cleanArray($message_data);
				$message_value = new TicketMessageValue();

				ArrayParserUtils::copyValueMapping(array(
					'oid'          => 'raw',
					'person'       => 'string',
					'date_created' => 'date',
					'message_html' => 'string',
					'message_text' => 'string',
					'is_note'      => 'bool'
				), $message_data, $message_value);

				if (!empty($message_data['attachments'])) {
					foreach ($message_data['attachments'] as $attachment_data) {
						$attachment_data = ArrayParserUtils::cleanArray($attachment_data);
						$attachment = new AttachmentValue();

						ArrayParserUtils::copyValueMapping(array(
							'oid'         => 'raw',
							'blob_data'    => 'raw',
							'blob_url'     => 'string',
							'blob_path'    => 'string',
							'filename'     => 'string',
							'content_type' => 'string',
							'is_inline'    => 'bool'
						), $attachment_data, $attachment);
						$message_value->attachments[] = $attachment;
					}
				}
				
				$value->messages[] = $message_value;
			}
		}
		
		if (!empty($data['custom_fields'])) {
			$index = 1;
			
			foreach ($data['custom_fields'] as $custom_field_data) {
				$custom_field_data = ArrayParserUtils::cleanArray($custom_field_data);
				
				$custom_def_value = new CustomDefValue();
				
				$keys = array_keys($custom_field_data);
				
				$custom_def_value->oid = $index++;

				$custom_def_value->key = $keys[0];
				
				$custom_def_value->value = $custom_field_data[$keys[0]];
				
				$value->custom_def[] = $custom_def_value;
			}
		}
		
		return $value;
	}
}