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
 * @subpackage AdminBundle
 */

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Application\AdminBundle\Form\EditLocaleType;

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
		$this->rememberLastPage();

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
			$locale = $this->em->getRepository('DeskPRO:Locale')->find($locale_id);
		}

		$form = $this->get('form.factory')->create(new EditLocaleType(), $locale);

		$is_edited = false;
		$row_html = false;
		if ($this->in->getBool('process')) {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				$is_edited = true;
				$this->em->persist($locale);
				$this->em->flush();

				$row_html = $this->renderView('AdminBundle:Locales:list-row.html.twig', array('locale' => $locale));
			}
		}

		return $this->render('AdminBundle:Locales:edit.html.twig', array(
			'locale' => $locale,
			'form'      => $form->createView(),
			'is_edited' => $is_edited,
			'row_html'  => $row_html
		));
	}
}
