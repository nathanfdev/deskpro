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
use Application\DeskPRO\Entity\ArticleCategory;
use Application\PortalBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class ArticlesController extends AbstractController
{
	public function indexAction()
	{
		return $this->render('Theme:Articles:index.html.twig');
	}


	public function browseAction($slug)
	{
		$id = substr($slug, 0, strpos($slug, '-'));
		$category = $this->getDoctrine()->getManager()->getRepository('DeskPRO:ArticleCategory')->find($id);
		return $this->render('Theme:Articles:browse.html.twig', array('category' => $category));
	}


    function viewAction(Article $slug)
	{
		return $this->render('Theme:Articles:view.html.twig', array('article' => $slug));
	}


	public function listAction(Request $request)
	{
		$cat = $request->get('cat');
		$category = $this->getDoctrine()->getManager()->getRepository('DeskPRO:ArticleCategory')->find($cat);
		$news  = $this->getArticlesRepo()->getNewest(null, $category);

		$template = 'Theme:Articles:list.html.twig';
		if ('small' == $request->query->get('style')) {
			$template = 'Theme:Articles:list_small.html.twig';
		}

		return $this->render(
			$template, array(
				'category' => $category,
				'articles'    => $news
			)
		);
	}


	public function categoriesAction(Request $request)
	{
		$cats  = $this->getArticleCategoryRepo()->findAll();

		$cat_articles = array();
		foreach ($cats as $cat) {
			$cat_articles[] = array('category' => $cat, 'articles' => $this->getArticlesRepo()->getInNode($cat));
		}

		return $this->render(
			'Theme:Articles:categories.html.twig', array(
				'data' => $cat_articles
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
