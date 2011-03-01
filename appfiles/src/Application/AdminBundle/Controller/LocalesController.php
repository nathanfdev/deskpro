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

use \Application\AdminBundle\Form\EditLocaleForm;

/**
 * Managing locales
 */
class LocalesController extends AbstractController
{
	############################################################################
	# list
	############################################################################

	/**
	 * Shows the main listing of departments
	 */
	public function listAction()
	{
		$all_locales = $this->em->createQuery("
			SELECT l
			FROM DeskPRO:Locale l
			LEFT JOIN l.language lang
			ORDER BY l.title ASC
		")->getResult();

		return $this->render('AdminBundle:Locales:list.html.twig', array(
			'all_locales' => $all_locales
		));
	}



	############################################################################
	# edit
	############################################################################

	/**
	 * Edit a department
	 */
	public function editAction($locale_id)
	{
		if (!$locale_id) {
			$locale = new Entity\Locale();
		} else {
			$locale = App::getEntityRepository('DeskPRO:Locale')->find($locale_id);
		}

		$form = EditLocaleForm::create($this->get('form.context'), 'locale', array('locale' => $locale));
		$form->bind($this->get('request'), $locale);

		$is_edited = false;
		$row_html = false;
		if ($this->in->getBool('process')) {
			$is_edited = true;
			App::getOrm()->persist($locale);
			App::getOrm()->flush();

			$row_html = $this->renderView('AdminBundle:Locales:list-row.html.twig', array('locale' => $locale));
		}

		return $this->render('AdminBundle:Locales:edit.html.twig', array(
			'locale' => $locale,
			'form'      => $form,
			'is_edited' => $is_edited,
			'row_html'  => $row_html
		));
	}
}