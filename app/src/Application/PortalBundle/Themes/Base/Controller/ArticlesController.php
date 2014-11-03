<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\PortalBundle\Themes\Base\Controller;


use Application\DeskPRO\Entity\Article;
use Application\PortalBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticlesController extends AbstractController
{
	public function indexAction()
	{
		return $this->render('Theme:Articles:index.html.twig');
	}


	public function browseAction($slug, Request $request)
	{
		/** @var \Application\DeskPRO\Entity\ArticleCategory $category */
		$id = substr($slug, 0, strpos($slug, '-'));
		$category = $this->getDoctrine()->getManager()->getRepository('DeskPRO:ArticleCategory')->find($id);

		// TODO: get core.portal.articles_per_page
		$per_page = 25;
		$page = $request->get('page', 1);

		// find breadcrumbs
		$breadcrumb_tree = $this->getArticleCategoryRepo()->gatherOrderedBreadcrumbTree($category);

		// fetch articles
		// TODO: this is actually a pretty decent way to query for the articles we need but need to be EXTRA_LAZY
		$articles = $category->articles->slice($per_page * ($page - 1), $per_page);

		return $this->render('Theme:Articles:browse.html.twig', array(
				'cat' => $category,
				'articles' => $articles,
				'breadcrumb_tree' => $breadcrumb_tree
			)
		);
	}


    function viewAction(Article $slug)
	{
		return $this->render('Theme:Articles:view.html.twig', array('article' => $slug));
	}


	public function listAction(Request $request)
	{
		$options_resolver = new OptionsResolver();
		$options_resolver
			->setRequired(array('category'))
			->setDefaults(
				array(
					'style'                 => 'small',
					'include_subcategories' => false,
					'count'                 => 10,
					'labelled'              => '',
					'sort'                  => 'date_published desc',
					'sort_by'               => function (Options $options) {
												$opts = explode(' ', $options['sort']);
												return isset($opts[0]) ? trim($opts[0]) : 'date_published';
											},
					'sort_direction'        => function (Options $options) {
												$opts = explode(' ', $options['sort']);
												return isset($opts[1]) ? trim($opts[1]) : 'desc';
											},
				)
			)
			->setAllowedValues(
				array(
					'style' => array('forcat', 'small', 'xsmall')
				)
			)
		;
		$options = $options_resolver->resolve($request->query->all());


		$data = $this->getArticlesRepo()->getDataForTagOptions($options);

		return $this->render(
			sprintf('Theme:Articles:list_%s.html.twig', $options['style']),
			array(
				'cat' => $data['cat'],
				'articles' => $data['articles'],
				'total_count' => $data['total_count']
			)
		);
	}


	public function categoriesAction(Request $request)
	{
		$options_resolver = new OptionsResolver();
		$options_resolver
			->setDefaults(
				array(
					'style'                 => 'small',
					'parent'                => null,
					'articles'              => array(
						'include_subcategories' => false
					)
				)
			)
			->setAllowedValues(
				array(
					'style' => array('expander', 'home', 'small', 'summary')
				)
			)
		;
		$options = $options_resolver->resolve($request->query->all());

		/** @var \Application\DeskPRO\EntityRepository\ArticleCategory $categories */
		if ($options['parent']) {
			$categories = array($this->getArticleCategoryRepo()->find($options['parent']));
		} else {
			$categories = $this->getArticleCategoryRepo()->findAll();
		}

		return $this->render(
			sprintf('Theme:Articles:cats_%s.html.twig', $options['style']),
			array(
				'cats' => $categories,
				'articles_options' => $options['articles']
			)
		);
	}


	/**
	 * @return \Application\DeskPRO\EntityRepository\Article
	 */
	protected function getArticlesRepo()
	{
		return $this->getDoctrine()->getRepository('DeskPRO:Article');
	}


	/**
	 * @return \Application\DeskPRO\EntityRepository\ArticleCategory
	 */
	protected function getArticleCategoryRepo()
	{
		return $this->getDoctrine()->getRepository('DeskPRO:ArticleCategory');
	}
}
