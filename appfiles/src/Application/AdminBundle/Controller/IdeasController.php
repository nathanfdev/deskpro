<?php

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\Entity\IdeaStatusCategory;
use Application\DeskPRO\Entity\IdeaCategory;
use Application\AdminBundle\Form\EditIdeaCategoryType;
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



	############################################################################
	# categories
	############################################################################

	public function categoriesAction()
	{
		$all_categories = $this->em->createQuery("
			SELECT c
			FROM DeskPRO:IdeaCategory c
			WHERE c.parent IS NULL
			ORDER BY c.display_order ASC
		")->getResult();

		return $this->render('AdminBundle:Ideas:cats.html.twig', array(
			'all_categories' => $all_categories
		));
	}

	public function editCategoryAction($category_id)
	{
		if (!$category_id) {
			$category = new IdeaCategory();
		} else {
			$category = $this->em->getRepository('DeskPRO:IdeaCategory')->find($category_id);
		}

		$form = $this->get('form.factory')->create(new EditIdeaCategoryType($category->id ? false : true), $category);

		if ($this->in->getBool('process')) {

			$do_move = false;
			if (!$category_id && $this->in->getUint('idea_cat.parent')) {
				$do_move = $this->db->fetchColumn("SELECT COUNT(*) FROM idea_categories c WHERE c.parent_id = ?", array($this->in->getUint('idea_cat.parent')));
			}

			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				$this->em->getConnection()->beginTransaction();

				try {
					$this->em->persist($category);
					$this->em->flush();

					if ($do_move) {
						$this->db->update('ideas', array('category_id' => $category->id), array('category_id' => $category->parent-id));
					}

					$this->em->getRepository('DeskPRO:IdeaCategory')->repair();

					$this->em->getConnection()->commit();
				} catch (\Exception $e) {
					$this->em->getConnection()->rollback();
					throw $e;
				}

				$this->session->setFlash('saved', $category->title);
				return $this->redirectRoute('admin_ideas_cats');
			}
		}

		$other_cats = $this->em->getRepository('DeskPRO:IdeaCategory')->getCategoriesInHierarchy();
		$exclude_cat_ids = $this->em->getRepository('DeskPRO:IdeaCategory')->getChildrenIds($category, false);
		$exclude_cat_ids[] = $category->id;

		$filter_fn = function($c) use ($exclude_cat_ids, &$filter_fn) {
			if (in_array($c['id'], $exclude_cat_ids)) {
				return false;
			}

			return true;
		};

		$trav_filter_fn = function(&$cats) use ($exclude_cat_ids, &$trav_filter_fn, &$filter_fn) {
			$cats = array_filter($cats, $filter_fn);

			foreach ($cats as &$v) {
				if ($v['children']) {
					$trav_filter_fn($v['children']);
				}
			}
		};
		$trav_filter_fn($other_cats);

		$count_existing = $this->em->getRepository('DeskPRO:Idea')->countInCategory($category);

		if (!$category_id) {
			$leaf_ids = $this->em->getRepository('DeskPRO:IdeaCategory')->getLeafIds();
		} else {
			$leaf_ids = array();
		}

		return $this->render('AdminBundle:Ideas:cats-edit.html.twig', array(
			'category' => $category,
			'form'      => $form->createView(),
			'count_existing' => $count_existing,
			'other_cats' => $other_cats,
			'leaf_ids' => $leaf_ids,
		));
	}

	public function deleteCategoryAction($category_id)
	{
		$category = $this->em->getRepository('DeskPRO:IdeaCategory')->find($category_id);

		$count_existing = $this->em->getRepository('DeskPRO:Idea')->countInCategory($category);

		if ($count_existing) {
			$move_cat = $this->em->getRepository('DeskPRO:IdeaStatusCategory')->find($this->in->getUint('move_to_cat'));
			if (!$move_cat) {
				$this->em->createQuery("
					SELECT c
					FROM DeskPRO:IdeaCategory c
					WHERE c != ?2
					ORDER BY c.id ASC
				")->setMaxResults(1)
				  ->setParameter(1, $category)
				  ->getOneOrNullResult();
			}

			if (!$move_cat) {
				return $this->renderStandardError("You did not specify a category to move existing ideas into.");
			}
		}

		$this->em->getConnection()->beginTransaction();
		try {

			if ($move_cat) {
				$this->db->update('ideas', array('category_id' => $move_cat->id), array('category_id' => $category->id));
			}

			foreach ($category->children as $c) {
				$this->em->remove($c);
			}
			$this->em->remove($category);
			$this->em->flush();

			$this->em->getRepository('DeskPRO:IdeaCategory')->repair();

			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		$this->session->setFlash('deleted', $category->title);
		return $this->redirectRoute('admin_ideas_cats');
	}

	public function updateCategoryOrdersAction()
	{
		$helper = new \Application\AdminBundle\Controller\Helper\DisplayOrderUpdate($this);
		return $helper->doUpdate('idea_categories');
	}
}
