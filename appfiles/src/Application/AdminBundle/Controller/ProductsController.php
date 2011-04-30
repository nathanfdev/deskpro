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

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Application\AdminBundle\Form\EditProductType;

/**
 * Simple management of products
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
		$this->rememberLastPage();

		$all_products = App::getOrm()->createQuery("
			SELECT p
			FROM DeskPRO:Product p
			ORDER BY p.title ASC
		")->execute();

		return $this->render('AdminBundle:Products:list.html.twig', array(
			'all_products' => $all_products
		));
	}



	############################################################################
	# edit
	############################################################################

	/**
	 * Edit a product
	 */
	public function editAction($product_id)
	{
		if (!$product_id) {
			$product = new Entity\Product();
		} else {
			$product = App::getEntityRepository('DeskPRO:Product')->find($product_id);
		}

		$form = $this->get('form.factory')->create(new EditProductType(), $product);

		$is_edited = false;
		$row_html = false;
		if ($this->in->getBool('process')) {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				$is_edited = true;
				App::getOrm()->persist($product);
				App::getOrm()->flush();

				$row_html = $this->renderView('AdminBundle:Products:list-row.html.twig', array('product' => $product));
			}
		}

		return $this->render('AdminBundle:Products:edit.html.twig', array(
			'product' => $product,
			'form'      => $form->createView(),
			'is_edited' => $is_edited,
			'row_html'  => $row_html
		));
	}
}