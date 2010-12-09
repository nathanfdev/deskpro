<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage XenForo
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Integration\XenForo\UserShare;

/**
 * Install code for the UserShare addon.
 */
class Install
{
	public static function install()
	{
		/*
		 Create table:

			CREATE TABLE `xf_deskpro_loginrequest` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `user_id` int(11) NOT NULL,
		  `user_key` varchar(255) NOT NULL,
		  `user_token` varchar(255) NOT NULL,
		  `access_token` varchar(255) NOT NULL,
		  `created_at` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM
		 */
		// TODO set default setting DeskPRO_UserShare_Key
	}

	public static function uninstall()
	{
		
	}
}