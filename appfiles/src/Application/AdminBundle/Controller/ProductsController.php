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
	 * Shows the main listing of products
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

	public function saveTitleAction()
	{
		$product_id = $this->in->getUint('product_id');
		$product = App::findEntity('DeskPRO:Product', $product_id);

		if (!$product) {
			throw $this->createNotFoundException();
		}

		if ($this->in->getString('title')) {
			$product->title = $this->in->getString('title');
		}

		$this->em->getConnection()->beginTransaction();

		try {
			$this->em->persist($product);
			$this->em->flush();

			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		return $this->redirectRoute('admin_products');
	}

	public function saveNewAction()
	{
		$product = new \Application\DeskPRO\Entity\Product();
		$product->title = $this->in->getString('title');

		if (!$product->title) {
			$product->title = 'Untitled';
		}

		if ($this->in->getUint('parent_id')) {
			$parent = App::findEntity('DeskPRO:Product', $this->in->getUint('parent_id'));
		}

		if ($parent and !$parent->parent) {
			$product->parent = $parent;
		}

		$this->em->getConnection()->beginTransaction();

		try {
			$this->em->persist($product);
			$this->em->flush();

			// Created first prod, enable the product feature
			$count = App::getDb()->fetchColumn("SELECT COUNT(*) FROM products");
			if ($count == 1) {
				App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.use_product', '1');
			}

			$this->em->getConnection()->commit();
		} catch (\Exception $e) {
			$this->em->getConnection()->rollback();
			throw $e;
		}

		return $this->redirectRoute('admin_products');
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

		$count = App::getDb()->fetchColumn("SELECT COUNT(*) FROM products");
		if (!$count) {
			App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.use_product', '0');
		}

		$this->em->commit();

		$this->session->setFlash('deleted', $product->title);
		return $this->redirectRoute('admin_products');
	}

	############################################################################
	# toggle-feature
	############################################################################

	public function toggleFeatureAction($enable)
	{
		if ($enable) {
			$count = App::getDb()->fetchColumn("SELECT COUNT(*) FROM products");
			if (!$count) {
				return $this->redirectRoute('admin_products');
			}

			App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.use_product', '1');
		} else {
				App::getEntityRepository('DeskPRO:Setting')->updateSetting('core.use_product', '0');
		}

		$url = $this->generateUrl('admin_products');
		if ($this->in->getString('return')) {
			$url = $this->in->getString('return');
		}

		return $this->redirect($url);
	}
}
