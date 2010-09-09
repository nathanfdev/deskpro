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

namespace Application\ApiBundle\StaticLoader;

class ApiKeyRequestLoader
{
	public static function getApiKeyFromRequest($container, \Symfony\Component\HttpFoundation\Request $request)
	{
		$api_key_str = $request->headers->get('X-DeskPRO-API-Key', true);
		if (!$api_key_str) {
			$api_key_str = isset($_GET['API-KEY']) ? $_GET['API-KEY'] : false;

			if (!$api_key_str) {
				return null;
			}
		}

		$em = $container->get('doctrine.orm.entity_manager');

		try {
			return $em->findOneBy('ApiBundle:ApiKey', array('api_key' => $api_key_str));
		} catch (\Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}
}