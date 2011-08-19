<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\DownloadComment;
use Application\DeskPRO\Searcher\DownloadSearch;
use Application\DeskPRO\UI\RuleBuilder;

use Application\AgentBundle\Controller\Helper\DownloadResults;

use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Util;
use Orb\Util\Numbers;

use FineDiff;

class DownloadsController extends AbstractController
{
	############################################################################
	# view
	############################################################################

	public function viewAction($download_id)
	{
		$download = App::findEntity('DeskPRO:Download', $download_id);
		$download_cats = App::getEntityRepository('DeskPRO:DownloadCategory')->getCategoryHelper()->getFlatHierarchy();
		$download_comments = App::getEntityRepository('DeskPRO:DownloadComment')->getComments($download);

		return $this->render('AgentBundle:Downloads:view.html.twig', array(
			'download'           => $download,
			'download_comments'  => $download_comments,
			'download_cats'      => $download_cats,
		));
	}

	public function ajaxSaveLabelsAction($download_id)
	{
		$download = App::findEntity('DeskPRO:Download', $download_id);

		$labels = $this->in->getCleanValueArray('labels', 'string', 'discard');

		$download->getLabelManager()->setLabelsArray($labels);

		App::getOrm()->persist($download);
		App::getOrm()->flush();

		return $this->createJsonResponse(array('success' => 1));
	}

	public function ajaxSaveCommentAction($download_id)
	{
		$download = App::findEntity('DeskPRO:Download', $download_id);

		$comment = new DownloadComment();
		$comment->download = $download;
		$comment->person = $this->person;
		$comment['content'] = $this->in->getString('content');
		$comment['status'] = 'visible';
		$comment['date_created']  = new \DateTime();

		App::getOrm()->persist($comment);
		App::getOrm()->flush();

		return $this->render('AgentBundle:Downloads:view-comment.html.twig', array(
			'comment' => $comment
		));
	}

	public function ajaxSaveAction($download_id)
	{
		$download = App::findEntity('DeskPRO:Download', $download_id);

		$action = $this->in->getString('action');

		$data = array('success' => 1);

		switch ($action) {
			case 'title':
				$download['title'] = $this->in->getString('title');
				break;

			case 'content':
				$download['content'] = $this->in->getString('content');
				$data['content_html'] = $download->getContentHtml();
				break;
		}

		App::getOrm()->persist($download);
		App::getOrm()->flush();

		return $this->createJsonResponse($data);
	}

	############################################################################
	# list
	############################################################################

	/**
	 * View a list of ideas
	 */
	public function listAction($category_id = 0)
	{
		$category = null;
		if ($category_id) {
			$category = App::findEntity('DeskPRO:DownloadCategory', $category_id);
		}

		$show_all = false;
		if (!$category) {
			$show_all = $this->in->getBool('all');
		}

		$result_helper = DownloadResults::newFromRequest($this, array(
			'category' => $category,
			'show_all' => $show_all
		));

		$page = $this->in->getUint('p');
		if (!$page) $page = 1;

		$results = $result_helper->getDownloadsForPage($page);
		$result_cache = $result_helper->getResultCache();

		$display_fields = $this->person->getPref('agent.ui.download-filter-display-fields.' . $result_cache['id']);
		if (!$display_fields) {
			$display_fields = array('author', 'date_created');
		}

		$tpl = 'AgentBundle:Downloads:filter.html.twig';
		if ($this->request->isPartialRequest()) {
			$tpl = 'AgentBundle:Downloads:filter-page.html.twig';
		}

		return $this->render($tpl, array(
			'results'            => $results,
			'result_id'          => $result_cache['id'],
			'display_fields'     => $display_fields,
			'category'           => $category,
		));
	}

	############################################################################
	# New download
	############################################################################

	public function newDownloadAction()
	{
		$download_categories = App::getEntityRepository('DeskPRO:DownloadCategory')->getCategoryHelper()->getFlatHierarchy();

		return $this->render('AgentBundle:Downloads:newdownload.html.twig', array(
			'download_categories' => $download_categories,
		));
	}

	public function newDownloadSaveAction()
	{
		$newdownload = new \Application\AgentBundle\Form\Model\NewDownload($this->person);

		$formType = new \Application\AgentBundle\Form\Type\NewDownlaod();
		$form = $this->get('form.factory')->create($formType, $newdownload);

		if ($this->get('request')->getMethod() == 'POST') {
			$form->bindRequest($this->get('request'));
			$form->isValid();

			$newdownload->save();

			$download = $newdownload->getDownload();

			return $this->createJsonResponse(array(
				'success' => true,
				'download_id' => $download['id']
			));
		} else {
			return $this->createJsonResponse(array(
				'success' => false,
			));
		}
	}
}