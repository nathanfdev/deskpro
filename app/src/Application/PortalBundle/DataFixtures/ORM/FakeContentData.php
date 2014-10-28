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

namespace Application\PortalBundle\DataFixtures\ORM;


use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackCategory;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\NewsCategory;
use Application\DeskPRO\Entity\DownloadCategory;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class FakeContentData implements FixtureInterface
{
	protected $person;
	/**
	 * Load data fixtures with the passed EntityManager
	 *
	 * @param \Doctrine\Common\Persistence\ObjectManager $manager
	 */
	function load(ObjectManager $manager)
	{
		$this->person = $manager->getRepository('DeskPRO:Person')->find(1);
		$this->news($manager);
		$this->knowledgebase($manager);
		$this->feedback($manager);
		$this->downloads($manager);

		$manager->flush();
	}


	/**
	 * @param ObjectManager $manager
	 */
	protected function knowledgebase(ObjectManager $manager)
	{
		$article_items = array(
			'Article 1',
			'Article 2',
			'Article 3',
			'Article 4',
			'Article 5',
			'Article 6',
			'Article 7',
			'Article 8',
			'Article 9',
			'Article 10',
			'Article 11',
			'Article 12',
			'Article 13',
			'Article 14',
			'Article 15',
		);

		$kb_category1        = new ArticleCategory();
		$kb_category1->title = 'One';
		$manager->persist($kb_category1);
		$kb_category2        = new ArticleCategory();
		$kb_category2->title = 'Two';
		$manager->persist($kb_category2);
		$kb_category3        = new ArticleCategory();
		$kb_category3->title = 'Three';
		$manager->persist($kb_category3);
		$kb_category4        = new ArticleCategory();
		$kb_category4->title = 'Four';
		$manager->persist($kb_category4);

		$i = 1;
		foreach ($article_items as $title) {
			if ($i > 4) $i = 1;
			$article           = new Article();
			$cat               = 'kb_category' . $i;
			$article->setCategories(array($$cat));
			$article->title    = $title;
			$article->person = $this->person;
			$article->content  = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut lobortis velit id lobortis aliquet. In vel elit vel ex ornare viverra. Morbi scelerisque arcu eros, at varius turpis vulputate non. Praesent ultrices pharetra mattis. Nullam euismod nunc nulla, in cursus nibh aliquet nec. Cras a hendrerit ex. Donec sit amet lectus eu turpis lobortis commodo. Curabitur ut consequat turpis, quis tempus ipsum. Aenean aliquam turpis ligula, eu ullamcorper purus ultricies quis. Fusce eleifend lorem vel eros aliquam pretium. Pellentesque sodales dictum nulla volutpat egestas.';
			$article->setStatus(Article::STATUS_PUBLISHED);
			$manager->persist($article);
			$i++;
		}
	}


	/**
	 * @param ObjectManager $manager
	 */
	protected function news(ObjectManager $manager)
	{
		$news_category = $manager->getRepository('DeskPRO:NewsCategory')->find(1);
		if (!$news_category) {
			$news_category        = new NewsCategory();
			$news_category->title = 'Tests';
			$manager->persist($news_category);
		}

		$news_items = array(
			'Google Makes Money',
			'Yahoo Wins Battle',
			'Dinosaurs Alive',
			'Homer Simpson Real',
			'New Feature: News',
			'Happy Friday',
			'Nintendo Sales Boom'
		);

		foreach ($news_items as $title) {
			$news           = new News();
			$news->category = $news_category;
			$news->title    = $title;
			$news->content  = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut lobortis velit id lobortis aliquet. In vel elit vel ex ornare viverra. Morbi scelerisque arcu eros, at varius turpis vulputate non. Praesent ultrices pharetra mattis. Nullam euismod nunc nulla, in cursus nibh aliquet nec. Cras a hendrerit ex. Donec sit amet lectus eu turpis lobortis commodo. Curabitur ut consequat turpis, quis tempus ipsum. Aenean aliquam turpis ligula, eu ullamcorper purus ultricies quis. Fusce eleifend lorem vel eros aliquam pretium. Pellentesque sodales dictum nulla volutpat egestas.';
			$news->setStatus(News::STATUS_PUBLISHED);
			$news->person = $this->person;
			$manager->persist($news);
		}
	}


	/**
	 * @param ObjectManager $manager
	 */
	protected function feedback(ObjectManager $manager)
	{
		$feedback_cat1 = $manager->getRepository('DeskPRO:FeedbackCategory')->find(1);
		if (!$feedback_cat1) $feedback_cat1 = $this->addFeedbackCat('Suggestion', $manager);
		$feedback_cat2 = $manager->getRepository('DeskPRO:FeedbackCategory')->find(2);
		if (!$feedback_cat2) $feedback_cat2 = $this->addFeedbackCat('Feature Request', $manager);
		$feedback_cat3 = $manager->getRepository('DeskPRO:FeedbackCategory')->find(3);
		if (!$feedback_cat3) $feedback_cat3 = $this->addFeedbackCat('Bug Report', $manager);

		$feedbacks = array(
			'I like it' => $feedback_cat1,
			'I dont like it, really' => $feedback_cat2,
			'Can it be be green?' => $feedback_cat3,
			'What is this feature?' => $feedback_cat1,
			'Ok, fair enough' => $feedback_cat1,
			'Woohoo!' => $feedback_cat2,
			'Latest Build' => $feedback_cat2
		);

		foreach ($feedbacks as $title => $cat) {
			$feedback           = new Feedback();
			$feedback->category = $cat;
			$feedback->title    = $title;
			$feedback->recalculatePopularity();
			$feedback->content  = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut lobortis velit id lobortis aliquet. In vel elit vel ex ornare viverra. Morbi scelerisque arcu eros, at varius turpis vulputate non. Praesent ultrices pharetra mattis. Nullam euismod nunc nulla, in cursus nibh aliquet nec. Cras a hendrerit ex. Donec sit amet lectus eu turpis lobortis commodo. Curabitur ut consequat turpis, quis tempus ipsum. Aenean aliquam turpis ligula, eu ullamcorper purus ultricies quis. Fusce eleifend lorem vel eros aliquam pretium. Pellentesque sodales dictum nulla volutpat egestas.';
			$feedback->setStatus(Feedback::STATUS_PUBLISHED);
			$feedback->person = $this->person;
			$manager->persist($feedback);
		}
	}


	/**
	 * @param ObjectManager $manager
	 */
	protected function downloads(ObjectManager $manager)
	{
		$download_cat = $manager->getRepository('DeskPRO:DownloadCategory')->find(1);
		if (!$download_cat) {
			$download_cat = new DownloadCategory();
			$download_cat->title = 'Documents';
			$manager->persist($download_cat);
		}

		$dl_names = array(
			'File 1',
			'File 2',
			'File 3',
			'File 4',
			'File 5',
			'File 6',
			'File 7',
		);

		foreach ($dl_names as $title) {
			$dl           = new Download();
			$dl->category = $download_cat;
			$dl->title    = $title;
			$dl->content  = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut lobortis velit id lobortis aliquet. In vel elit vel ex ornare viverra. Morbi scelerisque arcu eros, at varius turpis vulputate non. Praesent ultrices pharetra mattis. Nullam euismod nunc nulla, in cursus nibh aliquet nec. Cras a hendrerit ex. Donec sit amet lectus eu turpis lobortis commodo. Curabitur ut consequat turpis, quis tempus ipsum. Aenean aliquam turpis ligula, eu ullamcorper purus ultricies quis. Fusce eleifend lorem vel eros aliquam pretium. Pellentesque sodales dictum nulla volutpat egestas.';
			$dl->setStatus(Download::STATUS_PUBLISHED);
			$dl->person = $this->person;
			$manager->persist($dl);
		}
	}


	private function addFeedbackCat($string, ObjectManager $manager)
	{
		$cat_feedback = new FeedbackCategory();
		$cat_feedback->title = $string;
		$manager->persist($cat_feedback);
		return $cat_feedback;
	}
}
 