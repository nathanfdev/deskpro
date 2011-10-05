<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Application\AdminBundle\Form\EditProductType;

/**
 * Products
 */
class ProductsController extends AbstractController
{
	############################################################################
	# list
	############################################################################

	/**
	 * Shows the main listing of departments
	 */
	public function listAction()
	{
		$all_products = $this->em->createQuery("
			SELECT p
			FROM DeskPRO:Product p
			WHERE p.parent IS NULL
			ORDER BY p.display_order ASC
		")->getResult();

		return $this->render('AdminBundle:Products:list.html.twig', array(
			'all_products' => $all_products
		));
	}



	############################################################################
	# edit
	############################################################################

	/**
	 * Edit a department
	 */
	public function editAction($product_id)
	{
		if (!$product_id) {
			$product = new Entity\Product();
		} else {
			$product = App::getEntityRepository('DeskPRO:Product')->find($product_id);
		}

		$form = $this->get('form.factory')->create(new EditProductType($product->id ? false : true), $product);

		if ($this->in->getBool('process')) {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				App::getOrm()->persist($product);
				App::getOrm()->flush();

				$this->session->setFlash('saved', $product->title);
				return $this->redirectRoute('admin_products');
			}
		}

		return $this->render('AdminBundle:Products:edit.html.twig', array(
			'product'   => $product,
			'form'      => $form->createView(),
		));
	}

	############################################################################
	# update-orders
	############################################################################

	public function updateOrdersAction()
	{
		$helper = new \Application\AdminBundle\Controller\Helper\DisplayOrderUpdate($this);
		return $helper->doUpdate('products');
	}

	############################################################################
	# delete
	############################################################################

	public function deleteAction($product_id)
	{
		$product = App::getEntityRepository('DeskPRO:Product')->find($product_id);

		return $this->render('AdminBundle:Products:delete.html.twig', array(
			'product'  => $product,
		));
	}

	public function doDeleteAction($product_id, $security_token)
	{
		$product = App::getEntityRepository('DeskPRO:Product')->find($product_id);

		if (!$this->session->getEntity()->checkSecurityToken('delete_product', $security_token)) {
			// TODO err
			die('invalid token');
		}

		$this->em->beginTransaction();
		foreach ($product->children as $c) {
			$this->em->remove($c);
		}
		$this->em->remove($product);
		$this->em->flush();
		$this->em->commit();

		$this->session->setFlash('deleted', $product->title);
		return $this->redirectRoute('admin_products');
	}
}
