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
 */

namespace Application\AgentBundle\Controller;

class TextSnippetsController extends AbstractController
{
	public function requireRequestToken($action, $arguments = null)
	{
		return false;
	}

	public function reloadClientAction($typename)
	{
		$snippet_cats = $this->em->getRepository('DeskPRO:TextSnippetCategory')->getCatsForAgent($typename, $this->person);

		foreach ($this->container->getLanguageData()->getAll() as $lang) {
			$this->container->getObjectLangRepository()->preloadObjectCollection($lang, $snippet_cats);
		}

		$snippets_count = $this->em->getRepository('DeskPRO:TextSnippet')->countSnippetsForAgent($typename, $this->person);
		$per_page       = 250;
		$num_pages      = ceil($snippets_count / $per_page);

		$data = array(
			'typename'       => $typename,
			'snippets_count' => $snippets_count,
			'num_pages'      => $num_pages,
			'snippet_cats'   => array(),
		);

		foreach ($snippet_cats as $cat) {
			$data['snippet_cats'][] = $cat->toApiData();
		}

		return $this->createJsonResponse($data);
	}

	public function reloadClientBatchAction($typename, $batch = 1)
	{
		$snippets = $this->em->getRepository('DeskPRO:TextSnippet')->getAllSnippetsForAgent($typename, $this->person, $batch, 250);
		foreach ($this->container->getLanguageData()->getAll() as $lang) {
			$this->container->getObjectLangRepository()->preloadObjectCollection($lang, $snippets);
		}

		$data = array('snippets' => array());
		foreach ($snippets as $snippet) {
			$data['snippets'][] = $snippet->toApiData();
		}

		return $this->createJsonResponse($data);
	}
}