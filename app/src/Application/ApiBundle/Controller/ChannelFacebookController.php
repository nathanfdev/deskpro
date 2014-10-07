<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;

use Application\ApiBundle\PermissionStrategy\AdminManagePermission;
use Application\ApiBundle\PermissionStrategy\MultiPermissions;
use Application\ApiBundle\PermissionStrategy\PassPermission;
use Application\DeskPRO\Entity\FacebookApp;
use Application\DeskPRO\Entity\FacebookPage;
use Application\DeskPRO\Facebook\EditPage;
use Application\DeskPRO\Facebook\Type\EditPageType;

class ChannelFacebookController extends AbstractController implements ProtectedControllerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getPermissionStrategy()
	{
		$multi = new MultiPermissions();
		$multi->addPermissionStrategy(new AdminManagePermission());
		$multi->addPermissionStrategy(new PassPermission(), 'listAction');

		return $multi;
	}


	####################################################################################################################
	# list facebook pages
	####################################################################################################################

	public function listAction()
	{
		$pages = $this->getFacebookPageRepo()->findAll();

		$data = $this->getContainer()->getSerializer()->serializeArray($pages);

		return $this->createApiResponse(array('facebook_pages' => $data));
	}


	####################################################################################################################
	# create a facebook page
	####################################################################################################################

	public function createAction()
	{
		$page_postdata = $this->in->getArrayValue('page');

		if (!isset($page_postdata['graph_id'])) {
			return $this->createApiErrorResponse('invalid_argument', 'graph_id of a page is required');
		}

		$fb_app_repo  = $this->container->getEm()->getRepository('DeskPRO:FacebookPage');
		$existing_page = $fb_app_repo->findOneBy(array('graph_id' => $page_postdata['graph_id']));

		if ($existing_page) {
			return $this->createApiErrorResponse('page_exists', 'this page already exists as a channel');
		}

		$existing_app = null;
		if (isset($page_postdata['app']) && isset($page_postdata['app']['app_id'])) {
			$fb_app_repo = $this->container->getEm()->getRepository('DeskPRO:FacebookApp');
			$existing_app = $fb_app_repo->findOneBy(array('app_id' => $page_postdata['app']['app_id']));
		}

		$page = new FacebookPage();
		$page->app = $existing_app ?: new FacebookApp();

		$model = new EditPage($page);
		$form = $this->createForm(new EditPageType(), $model);
		$form->submit($page_postdata, true);

		$model->save($this->container->getEm());

		$data = $this->getContainer()->getSerializer()->serialize($page);

		return $this->createApiSuccessResponse($data);
	}


	####################################################################################################################
	# get facebook page
	####################################################################################################################

	public function getAction($id)
	{
		$page = $this->getFacebookPageRepo()->find($id);

		if (!$page) {
			return $this->createApiErrorResponse('not_found', sprintf('facebook page (id=%s) does not exist', $id));
		}

		$data = $this->getContainer()->getSerializer()->serialize($page);

		return $this->createApiResponse($data);
	}


	####################################################################################################################
	# save facebook page
	####################################################################################################################

	public function saveAction($id = null)
	{
		if (!$id) {
			return $this->createApiErrorResponse('invalid_argument', 'ID not passed');
		}

		$page = $this->getFacebookPageRepo()->find($id);

		if (!$page) {
			return $this->createApiErrorResponse('facebook.page_not_found', 'facebook page not found', 404);
		}

		$model = new EditPage($page);
		$form  = $this->createForm(new EditPageType(), $model);

		$page_postdata = $this->in->getArrayValue('page');
		$form->submit($page_postdata, true);

		$model->save($this->container->getEm());
		$data = $this->getContainer()->getSerializer()->serialize($page);

		return $this->createApiSuccessResponse($data);
	}

	####################################################################################################################
	# delete facebook page
	####################################################################################################################

	public function deleteAction($id)
	{
		$page = $this->getFacebookPageRepo()->find($id);

		if (!$page) {
			return $this->createApiErrorResponse('not_found', sprintf('facebook page (id=%s) does not exist', $id));
		}

		$em = $this->getContainer()->getEm();
		$em->remove($page);
		$em->flush();

		return $this->createApiSuccessResponse();
	}


	/**
	 * @return \Doctrine\ORM\EntityRepository
	 */
	private function getFacebookPageRepo()
	{
		return $this->getContainer()->getEm()->getRepository('DeskPRO:FacebookPage');
	}


	/**
	 * @param $account
	 */
	protected function saveFacebookPage(FacebookPage $account)
	{
		$this->getContainer()->getEm()->persist($account);
		$this->getContainer()->getEm()->flush();
	}
}
