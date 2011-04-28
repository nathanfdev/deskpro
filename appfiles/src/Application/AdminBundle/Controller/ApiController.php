<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AdminBundle\Controller;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

/**
 * Handles creating/editing of API keys
 */
class ApiController extends AbstractController
{
	############################################################################
	# index
	############################################################################

	/**
	 * Shows existing keys
	 */
	public function indexAction()
	{
		$this->rememberLastPage();

		$all_apikeys = $this->em->createQuery("
			SELECT k
			FROM DeskPRO:ApiKey k
			ORDER BY k.id ASC
		")->getResult();

		return $this->render('AdminBundle:Api:index.html.twig', array(
			'all_apikeys' => $all_apikeys
		));
	}



	############################################################################
	# edit
	############################################################################

	/**
	 * Edit an API Key
	 */
	public function editKeyAction($id)
	{
		if ($id) {
			$apikey = $this->getApiKeyOr404($id);
		} else {
			$apikey = new Entity\ApiKey();
		}

		if ($this->isPostRequest()) {
			$apikey['note'] = $this->in->getString('api_key.note');
			$apikey['person'] = App::getEntityRepository('DeskPRO:Person')->findOneByEmail($this->in->getString('person_email'));

			App::getOrm()->persist($apikey);
			App::getOrm()->flush();
		}

		return $this->render('AdminBundle:Api:edit-key.html.twig', array(
			'apikey' => $apikey
		));
	}



	############################################################################
	# delete
	############################################################################

	/**
	 * Delete an API Key
	 */
	public function delKeyAction($id)
	{
		$apikey = $this->getApiKeyOr404($id);

		App::getOrm()->remove($apikey);
		App::getOrm()->flush();

		return $this->redirectRoute('admin_api_keylist');
	}



	############################################################################

	/**
	 * @return Application\DeskPRO\Entity\ApiKey
	 */
	protected function getApiKeyOr404($id)
	{
		$apikey = App::getEntityRepository('DeskPRO:ApiKey')->find($id);
		if (!$apikey) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no API Key with ID $id");
		}

		return $apikey;
	}
}