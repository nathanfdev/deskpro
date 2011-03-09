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

use \Application\AdminBundle\Form\EditProductForm;

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

		$form = EditProductForm::create($this->get('form.context'), 'product', array('product' => $product));
		$form->bind($this->get('request'), $product);

		$is_edited = false;
		$row_html = false;
		if ($this->in->getBool('process')) {
			$is_edited = true;
			App::getOrm()->persist($product);
			App::getOrm()->flush();

			$row_html = $this->renderView('AdminBundle:Products:list-row.html.twig', array('product' => $product));
		}

		return $this->render('AdminBundle:Products:edit.html.twig', array(
			'product' => $product,
			'form'      => $form,
			'is_edited' => $is_edited,
			'row_html'  => $row_html
		));
	}
}