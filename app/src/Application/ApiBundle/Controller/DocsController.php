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

/**
 * Docs API Controller
 */
class DocsController extends AbstractController
{
	public function preAction($action, $arguments = null)
	{
		return null;
	}

	####################################################################################################################
	# about
	####################################################################################################################

	public function aboutAction()
	{
		return $this->render('ApiBundle:SwaggerUi:about.html.twig');
	}

	####################################################################################################################
	# api
	####################################################################################################################

	public function apiAction()
	{
		return $this->render('ApiBundle:SwaggerUi:api.html.twig');
	}

	####################################################################################################################
	# list
	####################################################################################################################

	public function listAction()
	{
		return $this->serveResource('deskpro-api');
	}

	####################################################################################################################
	# get
	####################################################################################################################

	public function getAction($id)
	{
		return $this->serveResource($id);
	}

	####################################################################################################################
	# get-agents-for-key
	####################################################################################################################

	public function getAgentsForKeyAction()
	{
		$apikey = $this->em->getRepository('DeskPRO:ApiKey')->findByKeyString($this->in->getString('key'));
		if ($apikey) {
			if ($apikey->isFlagSet('super')) {
				$agents = $this->container->getAgentData()->getNames();
			} else {
				$agents = array();
				$agents[$apikey->person->id] = $apikey->person->getDisplayName();
			}
			$default_id = $apikey->person ? $apikey->person->id : 0;
		} else {
			$agents = array();
			$default_id = 0;
		}

		return $this->createJsonResponse(array(
			'names'      => $agents,
			'default_id' => $default_id,
		));
	}

	####################################################################################################################

	private function getResourcePath($res)
	{
		return DP_ROOT.'/src/Application/ApiBundle/Resources/views/SwaggerDocs/' . ltrim($res, '/') . '.json';
	}

	private function serveResource($res)
	{
		$path = $this->getResourcePath($res);
		if (!file_exists($path)) {
			throw $this->createNotFoundException();
		}

		return $this->createJsonResponse(file_get_contents($path));
	}
}
