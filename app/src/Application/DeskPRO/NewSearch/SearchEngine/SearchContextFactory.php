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

namespace Application\DeskPRO\NewSearch\SearchEngine;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\DependencyInjection\DeskproContainer;

class SearchContextFactory
{
	/**
	 * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	private $container;


	/**
	 * @param DeskproContainer $container
	 */
	public function __construct(DeskproContainer $container)
	{
		$this->container = $container;
	}


	/**
	 * @param Person $person
	 * @return SearchContextInterface
	 */
	public function createUserSearchContext(Person $person)
	{
		$context = new SearchContext();

		$person->loadHelper('PermissionsManager');

		if ($person->hasPerm('articles.use')) {
			$ids = $person->PermissionsManager->ArticleCategories->getAllowedCategories();
			$context->setArticleCategoryIds($ids);
		}
		if ($person->hasPerm('feedback.use')) {
			$ids = $person->PermissionsManager->FeedbackCategories->getAllowedCategories();
			$context->setFeedbackCategoryIds($ids);
		}
		if ($person->hasPerm('news.use')) {
			$ids = $person->PermissionsManager->NewsCategories->getAllowedCategories();
			$context->setNewsCategoryIds($ids);
		}
		if ($person->hasPerm('download.use')) {
			$ids = $person->PermissionsManager->DownloadCategories->getAllowedCategories();
			$context->setDownloadCategoryIds($ids);
		}

		return $context;
	}
}