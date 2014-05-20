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

namespace Application\DeskPRO\WidgetSelections;

use Application\DeskPRO\App;
use Application\DeskPRO\CacheInvalidator\UserPageCache;
use Doctrine\ORM\EntityManager;

class WidgetSelections
{
	/**
	 * @var \Application\DeskPRO\ORM\EntityManager
	 */

	protected $em;

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	public function getWidgetSelections()
	{
		$articles  = App::getDb()->fetchAllKeyValue("SELECT id, title FROM articles WHERE status = 'published'");
		$downloads = App::getDb()->fetchAllKeyValue("SELECT id, title FROM downloads WHERE status = 'published'");
		$news      = App::getDb()->fetchAllKeyValue("SELECT id, title FROM news WHERE status = 'published'");

		$article_cat_map  = App::getDb()->fetchAllGrouped(
			"SELECT category_id, article_id FROM article_to_categories",
			array(),
			'category_id',
			null,
			'article_id'
		);

		$download_cat_map = App::getDb()->fetchAllGrouped(
			"SELECT category_id, id FROM downloads",
			array(),
			'category_id',
			null,
			'id'
		);

		$news_cat_map     = App::getDb()->fetchAllGrouped(
			"SELECT category_id, id FROM news",
			array(),
			'category_id',
			null,
			'id'
		);

		$selections = App::getEntityRepository('DeskPRO:DataStore')->getByName('portal_widget_default_links');

		if ($selections) {

			$selections = $selections->getData('selections');

		} else {

			$selections = array();
		}

		function buildTree($categories) {

			$result = array();

			foreach($categories as $category) {

				$data = array();

				$data['id']       = $category->id;
				$data['title']    = $category->title;
				$data['depth']    = $category->depth;
				$data['children'] = buildTree($category->children);

				$result[] = $data;
			}

			return $result;
		}

		return array(
			'selections'          => $selections,

			'article_categories'  => buildTree(App::getDataService('ArticleCategory')->getRootNodes()),
			'download_categories' => buildTree(App::getDataService('DownloadCategory')->getRootNodes()),
			'news_categories'     => buildTree(App::getDataService('NewsCategory')->getRootNodes()),

			'articles'            => $articles,
			'downloads'           => $downloads,
			'news'                => $news,

			'article_cat_map'     => $article_cat_map,
			'download_cat_map'    => $download_cat_map,
			'news_cat_map'        => $news_cat_map
		);
	}

	public function saveSelections($selections)
	{
		$ds = App::getEntityRepository('DeskPRO:DataStore')->getByName('portal_widget_default_links', true);
		$ds->setData('selections', $selections);

		$this->em->persist($ds);
		$this->em->flush();

		$cache = new UserPageCache();
		$cache->invalidateAll();
	}
}