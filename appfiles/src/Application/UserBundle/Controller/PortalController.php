<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @category Controllers
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\UserBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\PortalPageDisplay;
use Application\DeskPRO\PageDisplay\Page\PortalPage;

use Application\UserBundle\Controller\Helper\ContentRating;

class PortalController extends AbstractController
{
    public function portalAction()
    {
        return $this->render('UserBundle:Portal:portal.html.twig', array(

		));
    }

	public function saveRatingAction($object_type, $object_id)
	{
		$entity_name = 'DeskPRO:' . ucfirst($object_type);
		$content_object = App::findEntity($entity_name, $object_id);

		$content_rating = new ContentRating($content_object, $this->person, $this->session->getVisitor());
		$content_rating->setRequest($this->request);

		$this->em->beginTransaction();
		$content_rating->setRating(
			$this->in->getInt('rating'),
			$this->in->getUint('log_search_id')
		);
		$this->em->flush();
		$this->em->commit();

		return $this->redirect($content_object->getLink());
	}
}
