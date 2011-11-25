<?php

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\Entity\IdeaStatusCategory;
use Orb\Util\Arrays;

class IdeasController extends AbstractController
{
	############################################################################
	# statuses
	############################################################################

	public function statusesAction()
	{
		$active_cats = $this->em->getRepository('DeskPRO:IdeaStatusCategory')->getActiveCategories();
		$closed_cats = $this->em->getRepository('DeskPRO:IdeaStatusCategory')->getClosedCategories();

		return $this->render('AdminBundle:Ideas:statuses.html.twig', array(
			'active_cats' => $active_cats,
			'closed_cats' => $closed_cats
		));
	}

	public function updateStatusOrdersAction()
	{
		$helper = new \Application\AdminBundle\Controller\Helper\DisplayOrderUpdate($this);
		return $helper->doUpdate('idea_status_categories');
	}

	public function ajaxNewStatusAction()
	{
		$title = $this->in->getString('cat.title');
		$type = $this->in->getString('cat.status_type');

		if (!in_array($type, array('active', 'closed'))) {
			$type = 'active';
		}

		$this->em->getConnection()->beginTransaction();

		try {
			$cat = new IdeaStatusCategory();
			$cat->title = $title;
			$cat->status_type = $type;
			$cat->display_order = 9999;

			$this->em->persist($cat);
			$this->em->flush();

			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		return $this->render('AdminBundle:Ideas:statuses-row.html.twig', array('cat' => $cat));
	}

	public function editStatusAction($category_id)
	{
		$cat = $this->getStatusOr404($category_id);

		$count_existing = $this->em->getRepository('DeskPRO:Idea')->countInStatusCategory($cat);

		if ($cat->status_type == 'active') {
			$other_cats = $this->em->getRepository('DeskPRO:IdeaStatusCategory')->getActiveCategories();
		} else {
			$other_cats = $this->em->getRepository('DeskPRO:IdeaStatusCategory')->getClosedCategories();
		}

		unset($other_cats[$cat->id]);

		return $this->render('AdminBundle:Ideas:status-edit.html.twig', array(
			'cat' => $cat,
			'count_existing' => $count_existing,
			'other_cats' => $other_cats,
		));
	}

	public function deleteStatusAction($category_id)
	{
		$cat = $this->getStatusOr404($category_id);
		$count_existing = $this->em->getRepository('DeskPRO:Idea')->countInStatusCategory($cat);

		$this->em->getConnection()->beginTransaction();

		try {

			if ($count_existing) {
				$move_cat = $this->em->getRepository('DeskPRO:IdeaStatusCategory')->find($this->in->getUint('move_to_cat'));
				if (!$move_cat) {
					$this->em->createQuery("
						SELECT c
						FROM DeskPRO:IdeaStatusCategory c
						WHERE c.status_type = ?1 AND c != ?2
						ORDER BY c.id ASC
					")->setMaxResults(1)
					  ->setParameter(1, $cat->status_type)
					  ->setParameter(2, $cat)
					  ->getOneOrNullResult();
				}

				if (!$move_cat) {
					return $this->renderStandardError("You did not specify a status to move existing ideas into.");
				}

				$this->db->update('ideas', array('status_category_id' => $move_cat->id), array('status_category_id' => $cat->id));
			}

			$this->em->remove($cat);
			$this->em->flush();

			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		return $this->redirectRoute('admin_ideas_statuses');
	}

	/**
	 * @return \Application\DeskPRO\Entity\Person
	 */
	public function getStatusOr404($id)
	{
		$cat = $this->em->find('DeskPRO:IdeaStatusCategory', $id);
		if (!$cat) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no status with ID $id");
		}

		return $cat;
	}

}
