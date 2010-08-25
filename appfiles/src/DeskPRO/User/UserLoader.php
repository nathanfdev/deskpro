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

class UserLoader
{
	public static function getUserFromRequest($container, \Symfony\Components\HttpFoundation\Request $request)
	{
		$user_id = null;

		$session = $request->getSession();
		$user_id = $session->getAttribute('auth_userid');

		$em = $container->get('doctrine.orm.entity_manager');
		$user = false;

		if ($user_id) {
			try {
				$user = $em->createQuery('
					SELECT u, p, e
					FROM Core:User u
					JOIN u.profile p
					JOIN p.email_addresses e
					WHERE u.id = ?1'
				)->setParameter(1, $user_id)->getSingleResult();
			} catch (\Doctrine\ORM\NoResultException $e) {}
		}

		if (!$user) {
			$user = new \DeskPRO\Bundle\Core\Entity\User();
			$user->loadAsGuest();
		}

		return $user;
	}
}