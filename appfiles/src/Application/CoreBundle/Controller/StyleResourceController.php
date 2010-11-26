<?php
/**
 * DeskPRO
 *
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\CoreBundle\Controller;
use Orb\Util\Web;

/**
 * Serve style resources
 */
class StyleResourceController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
	public function servAction($resource_id, $resource_filename)
	{
		/** @var Doctrine\ORM\EntityManager */
		$em = $this->get('doctrine.orm.entity_manager');

		/** @var Application\CoreBundle\Entity\StyleResource */
		$res = $em->find('CoreBundle:StyleResource', $resource_id);
		if (!$res) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no resource with ID $resource_id");
		}

		// Make sure the URL is correct (consistency)
		if ($resource_filename != $res->getFilename()) {
			$this->redirect($this->generateUrl('core_styleres_serv', array('resource_id' => $resource_id, 'resource_filename' => $res->getFilename())));
		}

		// TODO
		// Right now mainly used for CSS so this is okay
		// But later we'll want to handle static caching, and handling
		// better output of large (ie images) files
		$content_fp = fopen('php://memory', 'r+');
		$res->writeResourceData($content_fp);

		$headers = $res->getResourceHeaders();

		$response = $this->createResponse($content_fp, 200, $headers);
		fclose($content_fp);

		return $response;
	}
}