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
			$apikey['person'] = $this->em->getRepository('DeskPRO:Person')->findOneByEmail($this->in->getString('person_email'));

			$this->em->persist($apikey);
			$this->em->flush();
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

		$this->em->remove($apikey);
		$this->em->flush();

		return $this->redirectRoute('admin_api_keylist');
	}



	############################################################################

	/**
	 * @return Application\DeskPRO\Entity\ApiKey
	 */
	protected function getApiKeyOr404($id)
	{
		$apikey = $this->em->getRepository('DeskPRO:ApiKey')->find($id);
		if (!$apikey) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no API Key with ID $id");
		}

		return $apikey;
	}
}
