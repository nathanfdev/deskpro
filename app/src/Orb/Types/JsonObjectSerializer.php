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
 * Orb
 *
 * @package Orb
 * @category Types
 */

namespace Orb\Types;

class JsonObjectSerializer
{
	/**
	 * @param JsonObjectSerializable $object
	 * @return string
	 */
	public static function serialize(JsonObjectSerializable $object)
	{
		$class_name = get_class($object);
		$obj_data   = $object->serializeJsonArray();
		$data = array(
			'@CLASS'   => $class_name,
			'@DATA'    => $obj_data
		);

		return json_encode($data);
	}


	/**
	 * @param string $json_object
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	public static function unserialize($json_object)
	{
		$data = json_decode($json_object, true);

		if (!isset($data['@CLASS']) || !isset($data['@DATA'])) {
			throw new \InvalidArgumentException("Not a valid JsonObjectSerializable serialized string");
		}

		$class_name = $data['@CLASS'];
		$object = $class_name::unserializeJsonArray($data['@DATA']);

		return $object;
	}
}