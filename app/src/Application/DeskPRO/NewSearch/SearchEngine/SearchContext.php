<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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

class SearchContext implements SearchContextInterface
{
	/**
	 * @var array
	 */
	private $article_category_ids  = array();

	/**
	 * @var array
	 */
	private $download_category_ids  = array();

	/**
	 * @var array
	 */
	private $news_category_ids     = array();

	/**
	 * @var array
	 */
	private $feedback_category_ids = array();


	/**
	 * @return array
	 */
	public function getArticleCategoryIds()
	{
		return $this->article_category_ids;
	}


	/**
	 * @param array $article_category_ids
	 */
	public function setArticleCategoryIds($article_category_ids)
	{
		$this->article_category_ids = $article_category_ids;
	}


	/**
	 * @return array
	 */
	public function getDownloadCategoryIds()
	{
		return $this->download_category_ids;
	}


	/**
	 * @param array $download_categyr_ids
	 */
	public function setDownloadCategoryIds($download_categyr_ids)
	{
		$this->download_category_ids = $download_categyr_ids;
	}


	/**
	 * @return array
	 */
	public function getFeedbackCategoryIds()
	{
		return $this->feedback_category_ids;
	}


	/**
	 * @param array $feedback_category_ids
	 */
	public function setFeedbackCategoryIds($feedback_category_ids)
	{
		$this->feedback_category_ids = $feedback_category_ids;
	}


	/**
	 * @return array
	 */
	public function getNewsCategoryIds()
	{
		return $this->news_category_ids;
	}


	/**
	 * @param array $news_category_ids
	 */
	public function setNewsCategoryIds($news_category_ids)
	{
		$this->news_category_ids = $news_category_ids;
	}
}