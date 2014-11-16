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

namespace Application\AdminInterfaceBundle\Controller;

use Application\DeskPRO\JIRA\OAuthWrapper;
use Application\DeskPRO\Service\JIRA;
use Symfony\Component\HttpFoundation\Request;

class JiraController extends AbstractController
{
	/**
	 * todo
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
	public function tokenAction(Request $request)
	{
		$oauth = new OAuthWrapper($this->get(JIRA::NAME), $this->generateUrl('jira_token', array(), true));

		$verifier = $request->get('oauth_verifier');
		$credentials = $request->getSession()->get('jira_oauth');

		if ($back = $request->get('back_url')) {
			$request->getSession()->set('jira_back_url', $back);
		}

		if ($verifier && $credentials) {

			$oauth->requestAuthCredentials(
				$credentials['oauth_token'],
				$credentials['oauth_token_secret'],
				$verifier
			);
			$request->getSession()->remove('jira_oauth');

			if ($back = $request->getSession()->get('jira_back_url')) {
				$request->getSession()->remove('jira_back_url');
			} else {
				$back = $this->generateUrl('jira_test');
			}

			return $this->redirect($back);
		}

		$credentials = $oauth->requestTempCredentials();
		$request->getSession()->set('jira_oauth', $credentials);

		return $this->redirect($oauth->getAuthUrl());
	}

	public function testAction()
	{
		/** @var JIRA $js */
		$js = $this->get(JIRA::NAME);
		$api = $js->getApi();

		$ret = $api->get('/issue/createmeta', array('expand' => 'projects.issuetypes.fields'));


//		$meta = $js->getMeta()->toArray();
//
//		$ret = $api->post('/search', array(
//			'jql' => sprintf('id IN (%s)', implode(',', array(10525, 10526))),
//			'fields' => $meta['default_fields_summary'],
//			'expand' => array('renderedFields'),
//		));

		echo '<script type="text/javascript">
			var a = ' . json_encode($ret) . ';
			console.log(a.projects[1].issuetypes[0].fields);
		</script>';
//		var_export($ret);

		die();
	}
}
