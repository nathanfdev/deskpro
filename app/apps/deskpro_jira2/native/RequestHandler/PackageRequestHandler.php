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
 * @category Entities
 */

namespace deskpro_jira2\RequestHandler;

use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestContext;
use Application\DeskPRO\App\Native\RequestHandler\ApiPackageRequestHandlerInterface;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\JIRA\OAuthWrapper;
use Application\DeskPRO\Service\JIRA;
use Guzzle\Http\Exception\ClientErrorResponseException;

class PackageRequestHandler implements ApiPackageRequestHandlerInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function handleApiPackageRequest(ApiPackageRequestContext $context)
	{
		switch ($context->getAction()) {
			case 'get-meta':
				return $this->getMetaAction($context);
			case 'set-meta':
				return $this->setMetaAction($context);
			default:
				throw $context->createNotFoundException();
		}
	}

	/**
	 * check api link connection
	 * @param DeskproContainer $container
	 * @return null
	 */
	protected function checkErrors(DeskproContainer $container)
	{
		$errors = array();

		/** @var JIRA $js */
		$js = $container->get('dp.jira');
		$back = $container->getRouter()->generateUrl('jira_token');
		$oauth = new OAuthWrapper($js, $back);
		try {
			$oauth->requestTempCredentials();
		} catch (\Exception $e) {

			if ($e instanceof ClientErrorResponseException) {
				$code = $e->getResponse()->getStatusCode();

				if (404 === $code || 403 === $code) {
					$errors['url'] = true;
				} else {
					$errors['api'] = true;
				}

			} else {
				$errors['url'] = true;
			}
		}

		if (!$errors && !$js->getTokens()) {
			$errors['token'] = true;
		}

		return $errors ?: null;
	}

	/**
	 * @param ApiPackageRequestContext $context
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function getMetaAction(ApiPackageRequestContext $context)
	{
		if ($errors = $this->checkErrors($context->getContainer())) {
			return $context->createJsonResponse(array('errors' => $errors));
		}

		/** @var JIRA $js */
		$js = $context->getContainer()->get('dp.jira');

		return $context->createJsonResponse(array('meta' => $js->getMeta()->toArray()));
	}

	/**
	 * @param ApiPackageRequestContext $context
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function setMetaAction(ApiPackageRequestContext $context)
	{
		/** @var JIRA $js */
		$js = $context->getContainer()->get('dp.jira');
		$data = (array) $context->getIn()->getAll('req');
		$meta = $js->updateMeta($data);

		return $context->createJsonResponse($meta->toArray());
	}
}