<?php

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\AdminBundle\Form\EditGatewayType;
use Orb\Util\Arrays;
use Symfony\Component\Form;

class EmailGatewaysController extends AbstractController
{
	############################################################################
	# list
	############################################################################

	/**
	 * Shows the main listing of gateways
	 */
	public function listAction()
	{
		$this->rememberLastPage();

		$all_gateways = $this->em->createQuery("
			SELECT g
			FROM DeskPRO:EmailGateway g
			ORDER BY g.name ASC
		")->getResult();

		return $this->render('AdminBundle:EmailGateways:list.html.twig', array(
			'all_gateways' => $all_gateways
		));
	}

	############################################################################
	# edit
	############################################################################

	/**
	 * Edit a gateway
	 */
	public function editAction($gateway_id)
	{
		if (!$gateway_id) {
			$gateway = new Entity\EmailGateway();
		} else {
			$gateway = App::getEntityRepository('DeskPRO:EmailGateway')->find($gateway_id);
		}

		$gateway['connection_class'] = 'Application\\DeskPRO\\EmailGateway\\Fetcher\\Pop3';
		$gateway['processor_class']  = 'Application\\DeskPRO\\EmailGateway\\TicketGateway';

		$form = $this->get('form.factory')->create(new EditGatewayType($gateway), $gateway);

		$is_edited = false;
		$row_html = false;
		if ($this->in->getBool('process')) {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				$is_edited = true;
				App::getOrm()->persist($gateway);
				App::getOrm()->flush();

				$row_html = $this->renderView('AdminBundle:EmailGateways:list-row.html.twig', array('gateway' => $gateway));
			}
		}

		return $this->render('AdminBundle:EmailGateways:edit.html.twig', array(
			'gateway' => $gateway,
			'form'      => $form->createView(),
			'is_edited' => $is_edited,
			'row_html'  => $row_html
		));
	}
}
