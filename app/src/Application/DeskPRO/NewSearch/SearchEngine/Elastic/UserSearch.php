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

namespace Application\DeskPRO\NewSearch\SearchEngine\Elastic;

use Application\DeskPRO\NewSearch\SearchEngine\Result\ResultSet;
use Application\DeskPRO\NewSearch\SearchEngine\SearchContextInterface;
use Application\DeskPRO\NewSearch\SearchEngine\UserSearchInterface;
use Elastica\Filter;
use Elastica\Query;

class UserSearch implements UserSearchInterface
{
	/**
	 * @var \Elastica\Index
	 */
	private $index;

	/**
	 * @var ElasticaResultsTransformer
	 */
	private $transformer;


	/**
	 * @param \Elastica\Index            $index
	 * @param ElasticaResultsTransformer $transformer
	 */
	public function __construct(\Elastica\Index $index, ElasticaResultsTransformer $transformer)
	{
		$this->index = $index;
		$this->transformer = $transformer;
	}


	/**
	 * @param SearchContextInterface $context
	 * @param string                 $query
	 * @param array                  $options
	 * @return ResultSet
	 */
	public function search(SearchContextInterface $context, $query, array $options = null)
	{
		$search = $this->index->createSearch();
		$filter = new Filter\BoolOr();

		if ($context->getArticleCategoryIds()) {
			$search->addType('article');
			$f = new Filter\Bool();
			$f->addMust(new Filter\Term(array('_type' => 'article')));
			$f->addMust(new Filter\Term(array('category_ids' => $context->getArticleCategoryIds())));
			$f->setBoost('1.5');
			$filter->addFilter($f);
		}
		if ($context->getNewsCategoryIds()) {
			$search->addType('news');
			$f = new Filter\Bool();
			$f->addMust(new Filter\Term(array('_type' => 'news')));
			$f->addMust(new Filter\Term(array('category_ids' => array($context->getNewsCategoryIds()))));
			$f->setBoost('1.3');
			$filter->addFilter($f);
		}
		if ($context->getDownloadCategoryIds()) {
			$search->addType('download');
			$f = new Filter\Bool();
			$f->addMust(new Filter\Term(array('_type' => 'download')));
			$f->addMust(new Filter\Term(array('category_ids' => array($context->getDownloadCategoryIds()))));
			$f->setBoost('1.5');
			$filter->addFilter($f);
		}
		if ($context->getFeedbackCategoryIds()) {
			$search->addType('feedback');
			$f = new Filter\Bool();
			$f->addMust(new Filter\Term(array('_type' => 'feedback')));
			$f->addMust(new Filter\Term(array('category_ids' => array($context->getFeedbackCategoryIds()))));
			$filter->addFilter($f);
		}

		if (!$search->getTypes()) {
			return new ResultSet();
		}

		$filtered_query = new Query\Filtered(new Query\QueryString($query), $filter);
		$res = $search->search($filtered_query);
		$objects = $this->transformer->transform($res->getResults());

		return new ResultSet($objects);
	}
}