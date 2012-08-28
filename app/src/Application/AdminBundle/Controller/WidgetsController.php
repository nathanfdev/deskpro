<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 */

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

/**
 * Handles creating/editing of widgets
 */
class WidgetsController extends AbstractController
{
	public function indexAction()
	{
		$repository = $this->_getWidgetRepository();

		return $this->render('AdminBundle:Widgets:index.html.twig', array(
			'widgetsGrouped' => $repository->getPageGroupedWidgets(),
			'pages' => $repository->getPages()
		));
	}

	public function toggleAction()
	{
		$this->ensureRequestToken();

		$updates = $this->in->getArray('widgets');
		$widgets = $this->_getWidgetRepository()->getByIds(array_keys($updates));

		$this->em->beginTransaction();

		foreach ($widgets AS $widget) {
			if (isset($updates[$widget->id])) {
				$widget->enabled = (bool)$updates[$widget->id];
				$this->em->persist($widget);
			}
		}

		try {
			$this->em->flush();
			$this->em->commit();
		} catch (\Exception $e) {
			$this->em->rollback();
			throw $e;
		}

		return $this->createJsonResponse(array(
			'ok' => 1
		));
	}

	public function deleteAction($widget_id)
	{
		$widget = $this->_getWidgetOr404($widget_id);

		if ($this->in->getBool('process')) {
			$this->ensureRequestToken();

			$this->em->beginTransaction();

			try {
				$this->em->remove($widget);
				$this->em->flush();
				$this->em->commit();
			} catch (\Exception $e) {
				$this->em->getConnection()->rollback();
				throw $e;
			}

			return $this->redirectRoute('admin_widgets');
		}

		return $this->render('AdminBundle:Widgets:delete.html.twig', array(
			'widget' => $widget
		));
	}

	public function editAction($widget_id)
	{
		if ($widget_id) {
			$widget = $this->_getWidgetOr404($widget_id);
		} else {
			$widget = new Entity\Widget();
		}

		$errors = array();

		if ($this->in->getBool('process')) {
			$this->ensureRequestToken();

			$description = $this->in->getString('description');
			$page = $this->in->getString('page');
			$insertPosition = $this->in->getString('insert_position');
			$location = $this->in->getString('page_location');

			$widget->description = $description;
			$widget->title = $this->in->getString('title');
			$widget->html = $this->in->getString('html');
			$widget->js = $this->in->getString('js');
			$widget->css = $this->in->getString('css');
			$widget->page = $page;
			$widget->page_location = $location;
			$widget->insert_position = $insertPosition;

			if (!$description) {
				$errors['description'] = 'Please enter a description.';
			}
			if (!$page || !$insertPosition || !$location) {
				$errors['page'] = 'Please enter a complete location.';
			}

			if (!$errors) {
				$this->em->beginTransaction();

				try {
					$this->em->persist($widget);
					$this->em->flush();
					$this->em->commit();
				} catch (\Exception $e) {
					$this->em->rollback();
					throw $e;
				}

				return $this->redirectRoute('admin_widgets');
			}
		}

		$repository = $this->_getWidgetRepository();

		return $this->render('AdminBundle:Widgets:edit.html.twig', array(
			'widget' => $widget,
			'errors' => $errors,
			'pages' => $repository->getPages(),
			'locations' => $repository->getPageLocations()
		));
	}



	############################################################################

	/**
	 * @param integer $id
	 *
	 * @return \Application\DeskPRO\Entity\Widget
	 */
	protected function _getWidgetOr404($id)
	{
		$apikey = $this->em->getRepository('DeskPRO:Widget')->find($id);
		if (!$apikey) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no widget with ID $id");
		}

		return $apikey;
	}

	/**
	 * @return \Application\DeskPRO\EntityRepository\Widget
	 */
	protected function _getWidgetRepository()
	{
		return $this->em->getRepository('DeskPRO:Widget');
	}
}
