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

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;
use Orb\Util\Arrays;
use Orb\Util\Numbers;

use Application\DeskPRO\Entity\EmailGateway;
use Application\DeskPRO\Entity\EmailSource;

class EmailGatewayErrorsController extends AbstractController
{
	####################################################################################################################
	# index
	####################################################################################################################

	public function indexAction()
	{
		$count = $this->em->getRepository('DeskPRO:EmailSource')->countForStatus(array('ticket', 'ticketmessage'), 'error');

		$per_page = 25;
		$p = $this->in->getUint('p');
		if (!$p) $p = 1;

		$pageinfo = Numbers::getPaginationPages($count, $p, $per_page, 5);

		$sources = $this->em->getRepository('DeskPRO:EmailSource')
		                 ->createQueryForTypeAndStatus(array('ticket', 'ticketmessage'), 'error')
		                 ->setFirstResult(($p - 1) * $per_page)
		                 ->setMaxResults($per_page)
		                 ->execute();

		error_log(count($sources));

		return $this->render('AdminBundle:EmailGatewayErrors:index.html.twig', array(
			'pageinfo'  => $pageinfo,
			'count'     => $count,
			'sources'   => $sources
		));
	}

	####################################################################################################################
	# view
	####################################################################################################################

	public function viewAction($id)
	{
		$source = $this->em->find('DeskPRO:EmailSource', $id);

		if (!$source) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$data_structure = null;
		if ($source->source_info) {
			$data_structure = print_r($source->source_info, true);
		}

		return $this->render('AdminBundle:EmailGatewayErrors:view.html.twig', array(
			'source' => $source,
			'data_structure' => $data_structure,
		));
	}

	####################################################################################################################
	# clear
	####################################################################################################################

	public function clearAction($security_token)
	{
		$this->ensureAuthToken('clear_gateway_errors', $security_token);

		$where = "object_type IN ('ticket', 'ticketmessage') AND status = 'error'";
		$blob_ids = App::getDb()->fetchAllCol("SELECT blob_id FROM email_sources WHERE $where AND blob_id IS NOT NULL");

		$this->db->executeUpdate("DELETE FROM email_sources WHERE $where");

		foreach ($blob_ids as $bid) {
			$desc = App::getApi('filestorage')->getFileDescriptor($bid);
			$desc->delete();
		}

		return $this->redirectRoute('admin_emailgateway_errors');
	}

	####################################################################################################################
	# delete
	####################################################################################################################

	public function deleteAction($id, $security_token)
	{
		$this->ensureAuthToken('delete_gateway_error', $security_token);

		$source = $this->em->find('DeskPRO:EmailSource', $id);

		if (!$source) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$this->em->remove($source);
		$this->em->flush();

		if ($source->blob) {
			$desc = App::getApi('filestorage')->getFileDescriptor($source->blob->getId());
			$desc->delete();
		}

		return $this->redirectRoute('admin_emailgateway_errors');
	}
}