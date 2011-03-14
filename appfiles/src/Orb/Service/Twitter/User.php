<?php

namespace Orb\Service\Twitter;

use \Application\DeskPRO\Entity\TwitterUser;

class User
{
	/**
	 * @param \SimpleXMLElement|\Zend_Rest_Client_Result $user
	 * @return \Application\DeskPRO\Entity\TwitterUser
	 */
	static public function createEntityFromXML($user)
	{
		// @TODO check against \SimpleXMLElement & \Zend_Rest_Client_Result

		$entity = new TwitterUser();
		$entity['id']                = (integer) $user->id;
		$entity['name']              = (string) $user->name;
		$entity['screen_name']       = (string) $user->screen_name;
		$entity['profile_image_url'] = (string) $user->profile_image_url;
		$entity['language']          = (string) $user->lang;
		$entity['is_protected']      = (Boolean) (integer) $user->protected;
		$entity['is_verified']       = (Boolean) (integer) $user->verified;
		$entity['location']          = (string) $user->location;
		$entity['is_geo_enabled']    = (Boolean) (integer) $user->geo_enabled;

		return $entity;
	}

	/**
	 * @param \array $user
	 * @return \Application\DeskPRO\Entity\TwitterUser
	 */
	static public function createEntityFromJson(array $user)
	{
		$entity = new TwitterUser();
		$entity['id']                = $user['id_str'];
		$entity['name']              = $user['name'];
		$entity['screen_name']       = $user['screen_name'];
		$entity['profile_image_url'] = $user['profile_image_url'];
		$entity['language']          = $user['lang'];
		$entity['is_protected']      = $user['protected'];
		$entity['is_verified']       = $user['verified'];
		$entity['location']          = $user['location'];
		$entity['is_geo_enabled']    = $user['geo_enabled'];

		return $entity;
	}
}
