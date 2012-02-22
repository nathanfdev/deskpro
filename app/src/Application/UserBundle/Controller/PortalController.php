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
		$show_portal_controls = $this->in->getBool('admin_portal_controls');

		if (!$show_portal_controls) {
			$portal_page = $this->container->get('deskpro.user_portal_page');

			// The user cant see anything on the page
			if (!$portal_page->getSectionDisplayItems('portal')) {
				if ($this->person->isGuest() && ($this->container->getSetting('core.user_mode') == 'require_reg' || $this->container->getSetting('core.user_mode') == 'require_reg_agent_validation')) {
					return $this->redirectRoute('user_login');
				} else {
					return $this->redirectRoute('user_tickets_new');
				}
			}
		}

        return $this->render('UserBundle:Portal:portal.html.twig', array(
			'admin_portal_controls' => $show_portal_controls
		));
    }

	public function saveRatingAction($object_type, $object_id)
	{
		$entity_name = 'DeskPRO:' . ucfirst($object_type);
		$content_object = App::findEntity($entity_name, $object_id);

		$content_rating = new ContentRating($content_object, $this->person, $this->session->getVisitor());
		$content_rating->setRequest($this->request);

		$rating = $this->in->getInt('rating');

		$this->em->beginTransaction();
		$content_rating->setRating(
			$rating,
			$this->in->getUint('log_search_id')
		);
		$this->em->flush();
		$this->em->commit();

		if ($this->session->has('preticket_id')) {
			$preticket = App::findEntity('DeskPRO:PreticketContent', $this->session->get('preticket_id'));
			$this->session->remove('preticket_id');

			if ($preticket) {
				if ($rating < 1) {
					$unsolved = $preticket->unsolved_content;
					$unsolved[] = array($object_type, $object_id);

					$preticket->unsolved_content = $unsolved;
				} else {
					$preticket->is_solved   = true;
					$preticket->object_type = $object_type;
					$preticket->object_id   = $object_id;
				}

				$this->em->beginTransaction();
				$this->em->persist($preticket);
				$this->em->flush();
				$this->em->commit();
			}
		}

		return $this->redirect($content_object->getLink());
	}

	public function newCommentFinishLoginAction($comment_type, $comment_id)
	{
		if ($this->person->isGuest()) {
			$return_url = $this->generateUrl('user_newcomment_finishlogin', array(
				'comment_type' => $comment_type,
				'comment_id' => $comment_id
			));
			return $this->redirectRoute('user_login', array('return' => $return_url));
		}

		switch ($comment_type) {
			case 'article': $entity = 'DeskPRO:ArticleComment'; break;
			case 'news': $entity = 'DeskPRO:NewsComment'; break;
			case 'download': $entity = 'DeskPRO:DownloadComment'; break;
			case 'feedback': $entity = 'DeskPRO:FeedbackComment'; break;
			default:
				throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
		}

		$comment = $this->em->find($entity, $comment_id);
		if (!$comment) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
		}

		$comment->status = 'validating';
		$this->em->getConnection()->beginTransaction();

		try {
			$this->em->persist($comment);
			$this->em->flush();

			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		return $this->redirect($comment->getObject()->getLink());
	}
}
