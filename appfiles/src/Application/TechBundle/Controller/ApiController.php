<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage TechBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\TechBundle\Controller;

/**
 * Handles creating/editing of API keys
 */
class ApiController extends AbstractController
{
	############################################################################
	# /tech/api                                                   tech_admin_api
	############################################################################

	/**
	 * Shows existing keys
	 */
	public function indexAction()
	{
		$all_apikeys = $this->em->createQuery("
			SELECT us
			FROM ApiBundle:ApiKey k
			ORDER BY k.id ASC
		")->getResult();

		if (!$all_apikeys->count()) {
			return $this->redirect($this->generateUrl('tech_admin_api_info', array()));
		}

		$this->tplvars['all_apikeys'] = $all_apikeys;

		return $this->render('TechBundle:Api:index');
	}



	############################################################################
	# /tech/api/intro                                       tech_admin_api_intro
	############################################################################

	/**
	 * Just shows a simple intro page
	 */
	public function introAction()
	{
		return $this->render('TechBundle:Api:intro');
	}



	############################################################################
	# /tech/api/key/:apikey/edit                          tech_admin_api_editkey
	############################################################################

	/**
	 * Edit an API Key
	 */
	public function editKeyAction($apikey)
	{
		if ($apikey) {
			$apikey = $this->getApiKeyOr404($apikey);
		} else {
			$apikey = $this->em->createEntity('ApiBundle:ApiKey');
		}

		if ($this->isPostRequest()) {

		}

		return $this->render('TechBundle:Api:edit-key');
	}



	############################################################################
	# /tech/api/key/:apikey/info                          tech_admin_api_keyinfo
	############################################################################

	/**
	 * Shows info about a key such as number of calls
	 */
	public function infoKeyAction($apikey)
	{
		$apikey = $this->getApiKeyOr404($apikey);

		// TODO

		return $this->render('TechBundle:Api:edit-key');
	}



	############################################################################

	/**
	 * @return Application\ApiBundle\Entity\ApiKey
	 */
	protected function getApiKeyOr404($apikey)
	{
		try {
			$apikey = $this->em->find('ApiBundle:ApiKey', $apikey);
		} catch (\Doctrine\ORM\NoResultException $e) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no API Key with ID $apikey");
		}

		return $apikey;
	}
}