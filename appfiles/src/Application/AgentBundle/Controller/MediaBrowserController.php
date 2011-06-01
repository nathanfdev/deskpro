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

/**
 * The mediabrowser does everything via ajax.
 */
class MediaBrowserController extends AbstractController
{
	############################################################################
	# get-current
	############################################################################
	
	public function getCurrentAction()
	{
		$ids = $this->in->getCleanValueArray('ids', 'uint', 'discard');

		if ($ids) {
			$blobs = App::getEntityRepository('DeskPRO:Blob')->getByIds($ids);
		} else {
			$blobs = array();
		}

		return $this->renderView('AgentBundle:MediaBrowser:current.html.twig', array(
			'blobs' => $blobs,
		));
	}

	
	############################################################################
	# get-recent
	############################################################################

	public function getRecentAction($type = false)
	{
		if ($type) {
			$recent_blob_objects = App::getEntityRepository('DeskPRO:BlobObjectAttach')->getRecent(30, $type);
		} else {
			$recent_blob_objects = App::getEntityRepository('DeskPRO:BlobObjectAttach')->getRecent(30);
		}

		return $this->renderView('AgentBundle:MediaBrowser:recent.html.twig', array(
			'type' => $type,
			'recent_blob_objects' => $recent_blob_objects,
		));
	}


	############################################################################
	# update-blob
	############################################################################

	public function updateBlobAction($blob_id)
	{
		/** @var $blob \Application\DeskPRO\Entity\Blob */
		$blob = App::findEntity('DeskPRO:Blob', $blob_id);

		$blob['title'] = $this->in->getString('title');
		$blob['is_media_upload'] = true;
		$blob->getLabelManager()->setLabelsArray($this->in->getCleanValueArray('labels', 'string', 'discard'));

		App::getOrm()->transactional(function ($em) use ($blob) {
			$em->persist($blob);
			$em->flush();
		});

		return $this->createJsonResponse(array('success' => true));
	}

	############################################################################
	# library
	############################################################################

	public function libraryAction($page = 1)
	{
		$types = $this->in->getCleanValueArray('types', 'string', 'discard');
		$labels = $this->in->getCleanValueArray('labels', 'string', 'discard');

		$qp = new \Application\DeskPRO\ORM\QueryPartial();
		$qp->setMaxResults(50)->setOrderBy('blob.id', 'DESC')->setFirstResult(($page-1) * 50);

		$blob_objects = App::getEntityRepository('DeskPRO:BlobObjectAttach')->getLibraryResults($types, $labels, $qp);

		return $this->renderView('AgentBundle:MediaBrowser:library.html.twig', array(
			'types' => $types,
			'labels' => $labels,
			'blob_objects' => $blob_objects,
		));
	}

	############################################################################
	# library-kb
	############################################################################

	public function libraryKbAction($category_id, $page = 1)
	{
		$labels = $this->in->getCleanValueArray('labels', 'string', 'discard');

		/** @var $category \Application\DeskPRO\Entity\ArticleCategory */
		$category = App::findEntity('DeskPRO:ArticleCategory', $category_id);
		$cat_ids = $category->getTreeIds(true);

		$qp = new \Application\DeskPRO\ORM\QueryPartial();
		$qp->setMaxResults(50)->setOrderBy('blob.id', 'DESC')->setFirstResult(($page-1) * 50);

		$blob_objects = App::getEntityRepository('DeskPRO:BlobObjectAttach')->getKbLibraryResults($cat_ids, $labels, $qp);

		$category_hierarchy = App::getEntityRepository('DeskPRO:ArticleCateogory')->getCategoryHelper()->getFlatHierarchy();

		return $this->renderView('AgentBundle:MediaBrowser:library.html.twig', array(
			'category_hierarchy' => $category_hierarchy,
			'labels' => $labels,
			'blob_objects' => $blob_objects,
		));
	}
}