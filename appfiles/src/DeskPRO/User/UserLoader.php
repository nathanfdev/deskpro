<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Controller
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\User;

class UserLaoder
{
	public function getUserFromRequest($container, Symfony\Components\HttpFoundation\Request $request)
	{
		$user_id = null;

		if ($request->hasSession()) {
			$session = $request->getSession();
			$user_id = $session->getAttribute('auth_userid');
		}

		if (!$user_id) {
			$user = new User();
			$user->loadAsGuest();
			return $user;
		}



		$em = $container->getService('doctrine.orm.entity_manager');
		$user = $em->createQuery('SELECT DeskPRO:User WHERE id = ?', $user_id);

		return $user;
	}
}