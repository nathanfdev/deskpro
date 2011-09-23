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

		$form = $this->get('form.factory')->create(new EditProductType($product), $product);

		$is_edited = false;
		$row_html = false;
		if ($this->in->getBool('process')) {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				$is_edited = true;
				App::getOrm()->persist($product);
				App::getOrm()->flush();

				$row_html = $this->renderView('AdminBundle:Products:list-row.html.twig', array('product' => $product));

				// Recreate form because parent_id field cant be changed, so we need to get rid of it
				$form = $this->get('form.factory')->create(new EditProductType($product), $product);
			}
		}

		return $this->render('AdminBundle:Products:edit.html.twig', array(
			'product'   => $product,
			'form'      => $form->createView(),
			'is_edited' => $is_edited,
			'row_html'  => $row_html
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

		$ids = array();

		App::getOrm()->beginTransaction();
		$this->_deleteProduct($product, $ids);
		App::getOrm()->flush();
		App::getOrm()->commit();

		$this->createJsonResponse(array(
			'success' => true,
			'deleted_ids' => $ids
		));
	}

	protected function _deleteProduct($product, array &$ids)
	{
		if ($product->children) {
			foreach ($product->children as $child) {
				$this->_deleteProduct($child, $ids);
			}
		}

		App::getOrm()->remove($product);
		$ids[] = $product['id'];
	}
}
