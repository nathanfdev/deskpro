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

namespace Application\DeskPRO\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Util;
use Orb\Util\Strings;

class Deskpro3RedirectController extends AbstractController
{
	public function redirectKbHomeAction()
	{
		return $this->redirectRoute('user_articles');
	}

	public function redirectKbAction()
	{
		$ref = isset($_GET['ref']) ? $_GET['ref'] : 0;
		$lookup = 'dp3_kbref_' . $ref;
		$new_id = $this->db->fetchColumn("SELECT data FROM import_datastore WHERE typename = ?", array($lookup));

		$article = null;
		if ($new_id) {
			$article = $this->em->find('DeskPRO:Article', $new_id);
		}

		if (!$article) {
			throw $this->createNotFoundException();
		}


		return $this->redirectRoute('user_articles_article', array('slug' => $article->getUrlSlug()), 301);
	}

	public function redirectKbCatAction()
	{
		$id = isset($_GET['id']) ? $_GET['id'] : 0;
		$lookup = 'dp3_kbcatid_' . $id;
		$new_id = $this->db->fetchColumn("SELECT data FROM import_datastore WHERE typename = ?", array($lookup));

		$cat = null;
		if ($new_id) {
			$cat = $this->em->find('DeskPRO:ArticleCategory', $new_id);
		}

		if (!$cat) {
			throw $this->createNotFoundException();
		}


		return $this->redirectRoute('user_articles', array('slug' => $cat->getUrlSlug()), 301);
	}

	public function redirectNewsAction()
	{
		$id = isset($_GET['id']) ? $_GET['id'] : 0;
		$lookup = 'dp3_newsid_' . $id;
		$new_id = $this->db->fetchColumn("SELECT data FROM import_datastore WHERE typename = ?", array($lookup));

		$news = null;
		if ($new_id) {
			$news = $this->em->find('DeskPRO:News', $new_id);
		}

		if (!$cat) {
			throw $this->createNotFoundException();
		}


		return $this->redirectRoute('user_news_view', array('slug' => $news->getUrlSlug()), 301);
	}

	public function redirectIdeaHomeAction()
	{
		return $this->redirectRoute('user_feedback');
	}

	public function redirectIdeaAction()
	{
		if (isset($_GET['cat'])) {
			return $this->redirectIdeaCatAction();
		}

		$id = isset($_GET[0]) ? $_GET[0] : 0;

		if (!$id) {
			return $this->redirectIdeaHomeAction();
		}

		$lookup = 'dp3_ideaid_' . $id;
		$new_id = $this->db->fetchColumn("SELECT data FROM import_datastore WHERE typename = ?", array($lookup));

		$feedback = null;
		if ($new_id) {
			$feedback = $this->em->find('DeskPRO:Feedback', $new_id);
		}

		if (!$cat) {
			throw $this->createNotFoundException();
		}


		return $this->redirectRoute('user_feedback_view', array('slug' => $feedback->getUrlSlug()), 301);
	}

	public function redirectIdeaCatAction()
	{
		$id = isset($_GET['id']) ? $_GET['id'] : 0;
		$lookup = 'dp3_kbcatid_' . $id;
		$new_id = $this->db->fetchColumn("SELECT data FROM import_datastore WHERE typename = ?", array($lookup));

		$cat = null;
		if ($new_id) {
			$cat = $this->em->find('DeskPRO:ArticleCategory', $new_id);
		}

		if (!$cat) {
			throw $this->createNotFoundException();
		}


		return $this->redirectRoute('user_feedback', array('slug' => $cat->getUrlSlug()), 301);
	}
}