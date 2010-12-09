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

/**
 * Handles creating/editing of API keys
 */
class ApiController extends AbstractController
{
	############################################################################
	# /agent/api                                                   agent_admin_api
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
			return $this->redirect($this->generateUrl('agent_admin_api_info', array()));
		}

		$this->tplvars['all_apikeys'] = $all_apikeys;

		return $this->render('AdminBundle:Api:index.twig');
	}



	############################################################################
	# /agent/api/intro                                       agent_admin_api_intro
	############################################################################

	/**
	 * Just shows a simple intro page
	 */
	public function introAction()
	{
		return $this->render('AdminBundle:Api:intro.twig');
	}



	############################################################################
	# /agent/api/key/:apikey/edit                          agent_admin_api_editkey
	############################################################################

	/**
	 * Edit an API Key
	 */
	public function editKeyAction($apikey)
	{
		if ($apikey) {
			$apikey = $this->getApiKeyOr404($apikey);
		} else {
			$apikey = new \Application\DeskPRO\Entity\ApiKey();
		}

		if ($this->isPostRequest()) {

		}

		return $this->render('AdminBundle:Api:edit-key.twig');
	}



	############################################################################
	# /agent/api/key/:apikey/info                          agent_admin_api_keyinfo
	############################################################################

	/**
	 * Shows info about a key such as number of calls
	 */
	public function infoKeyAction($apikey)
	{
		$apikey = $this->getApiKeyOr404($apikey);

		// TODO

		return $this->render('AdminBundle:Api:edit-key.twig');
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