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
use Application\DeskPRO\Entity;
use Application\AdminBundle\Form\EditLanguageType;
use Orb\Util\Arrays;
use Symfony\Component\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Application\DeskPRO\Languages\LanguagePackFile;
use Application\DeskPRO\Languages\LanguagePack;
use Application\DeskPRO\Languages\LanguageInstaller;

class LanguagesController extends AbstractController
{
	############################################################################
	# list
	############################################################################

	public function indexAction()
    {
		$languages = $this->em->getRepository('DeskPRO:Language')->findAll();

		if ($this->in->checkIsset('set_enable_languages')) {
			$this->container->getSettingsHandler()->setSetting('core.enable_languages', $this->in->getBool('set_enable_languages'));
			return $this->redirectRoute('admin_langs');
		}

		if (!$this->container->getSetting('core.enable_languages')) {
			return $this->render('AdminBundle:Languages:landing-enable.html.twig', array());
		}

        return $this->render('AdminBundle:Languages:index.html.twig', array(
			'languages' => $languages,
		));
	}

	############################################################################
	# install
	############################################################################

	public function installAction()
	{
		$langpacks = new \Application\DeskPRO\Languages\LangPackInfo();
		$packs = $langpacks->getLangTitles();

		$installed_packs = $this->db->fetchAllKeyValue("
			SELECT sys_name, id
			FROM languages
		");

		return $this->render('AdminBundle:Languages:install.html.twig', array(
			'packs' => $packs,
			'installed_packs' => $installed_packs,
		));
	}

	public function installPackAction($id)
	{
		$langpacks = new \Application\DeskPRO\Languages\LangPackInfo();

		if (!$langpacks->hasLang($id)) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$lang = new \Application\DeskPRO\Entity\Language();
		$lang->sys_name      = $langpacks->getLangInfo($id, 'id');
		$lang->title         = $langpacks->getLangInfo($id, 'title');
		$lang->lang_code     = $langpacks->getLangInfo($id, 'lang_code');
		$lang->locale        = $langpacks->getLangInfo($id, 'locale');
		$lang->has_user      = $langpacks->getLangInfo($id, 'has_user');
		$lang->has_agent     = $langpacks->getLangInfo($id, 'has_agent');
		$lang->has_admin     = $langpacks->getLangInfo($id, 'has_admin');
		$lang->base_filepath = '%DP_ROOT%/languages/' . $id;

		$this->db->beginTransaction();
		try {
			$this->em->persist($lang);
			$this->em->flush();
			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
		}

		return $this->redirectRoute('admin_langs_editlang', array('language_id' => $lang->getId()));
	}

	############################################################################
	# edit-departments
	############################################################################

	public function departmentsAction($language_id)
	{
		$vars = $this->getLangInfo($language_id);

		$all_departments = $this->em->createQuery("
			SELECT dep
			FROM DeskPRO:Department dep
			WHERE dep.parent IS NULL
			ORDER BY dep.display_order ASC
		")->getResult();

		$vars['all_departments'] = $all_departments;

		$group = 'obj_department';
		$vars['lang_phrases'] = $this->em->getRepository('DeskPRO:Phrase')->getLanguagePhrasesInGroup($vars['language'], $group);

		return $this->render('AdminBundle:Languages:lang-phrases-departments.html.twig', $vars);
	}

	############################################################################
	# edit-products
	############################################################################

	public function productsAction($language_id)
	{
		$vars = $this->getLangInfo($language_id);

		$all_products = $this->em->createQuery("
			SELECT prod
			FROM DeskPRO:Product prod
			WHERE prod.parent IS NULL
			ORDER BY prod.display_order ASC
		")->getResult();

		$vars['all_products'] = $all_products;

		$group = 'obj_department';
		$vars['lang_phrases'] = $this->em->getRepository('DeskPRO:Phrase')->getLanguagePhrasesInGroup($vars['language'], $group);

		return $this->render('AdminBundle:Languages:lang-phrases-products.html.twig', $vars);
	}

	############################################################################
	# edit-ticket-categories
	############################################################################

	public function ticketCategoriesAction($language_id)
	{
		$vars = $this->getLangInfo($language_id);

		$all_categories = $this->em->createQuery("
			SELECT cat
			FROM DeskPRO:ticketCategory cat
			WHERE cat.parent IS NULL
			ORDER BY cat.display_order ASC
		")->getResult();

		$vars['all_categories'] = $all_categories;

		$group = 'obj_department';
		$vars['lang_phrases'] = $this->em->getRepository('DeskPRO:Phrase')->getLanguagePhrasesInGroup($vars['language'], $group);

		return $this->render('AdminBundle:Languages:lang-phrases-ticket-categories.html.twig', $vars);
	}

	############################################################################
	# edit-ticket-priorities
	############################################################################

	public function ticketPrioritiesAction($language_id)
	{
		$vars = $this->getLangInfo($language_id);

		$all_priorities = $this->em->createQuery("
			SELECT pri
			FROM DeskPRO:TicketPriority pri
			ORDER BY pri.priority ASC
		")->getResult();

		$vars['all_priorities'] = $all_priorities;

		$group = 'obj_ticketpriority';
		$vars['lang_phrases'] = $this->em->getRepository('DeskPRO:Phrase')->getLanguagePhrasesInGroup($vars['language'], $group);

		return $this->render('AdminBundle:Languages:lang-phrases-ticket-priorities.html.twig', $vars);
	}

	############################################################################
	# edit-ticket-workflows
	############################################################################

	public function ticketWorkflowsAction($language_id)
	{
		$vars = $this->getLangInfo($language_id);

		$all_priorities = $this->em->createQuery("
			SELECT work
			FROM DeskPRO:TicketWorkflow work
			ORDER BY work.display_order ASC
		")->getResult();

		$vars['all_priorities'] = $all_priorities;

		$group = 'obj_ticketworkflow';
		$vars['lang_phrases'] = $this->em->getRepository('DeskPRO:Phrase')->getLanguagePhrasesInGroup($vars['language'], $group);

		return $this->render('AdminBundle:Languages:lang-phrases-ticket-workflows.html.twig', $vars);
	}

	############################################################################
	# custom-ticket-fields
	############################################################################

	public function customFieldsAction($language_id, $field_type)
	{
		switch ($field_type) {
			case 'tickets':
				$ent = 'DeskPRO:CustomDefTicket';
				$group = 'obj_customdefticket';
				break;

			case 'people':
				$ent = 'DeskPRO:CustomDefPerson';
				$group = 'obj_customdefperson';
				break;

			case 'organizations':
				$ent = 'DeskPRO:CustomDefOrganization';
				$group = 'obj_customdeforganization';
				break;

			default: throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$vars = $this->getLangInfo($language_id);

		$all_fields = $this->em->createQuery("
			SELECT f
			FROM $ent f
			WHERE f.parent IS NULL
			ORDER BY f.display_order ASC
		")->getResult();

		$vars['all_fields'] = $all_fields;
		$vars['lang_group'] = $group;

		$vars['lang_phrases'] = $this->em->getRepository('DeskPRO:Phrase')->getLanguagePhrasesInGroup($vars['language'], $group);

		return $this->render('AdminBundle:Languages:lang-phrases-fields.html.twig', $vars);
	}

	############################################################################
	# edit-language
	############################################################################

	public function editLanguageAction($language_id)
	{
		$vars = $this->getLangInfo($language_id);

		if ($this->in->getBool('process')) {
			$lang = $vars['language'];
			$lang->title = $this->in->getString('language.title');
			$lang->locale = $this->in->getString('language.locale');
			$this->em->persist($lang);
			$this->em->flush();
		}

		$form = $this->get('form.factory')->create(new EditLanguageType(), $vars['language']);
		$vars['form'] = $form->createView();

		return $this->render('AdminBundle:Languages:lang-edit.html.twig', $vars);
	}

	public function deleteLanguageAction($language_id, $security_token)
	{
		if (!$this->session->getEntity()->checkSecurityToken('delete_lang', $security_token)) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$language = $this->getLanguageOr404($language_id);

		if ($language->id == 1) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$this->em->beginTransaction();
		try {

			// It was default, so set it back to English
			if ($this->container->getDataService('Language')->getDefault()->getId() == $language->getId()) {
				$this->container->getSettingsHandler()->setSetting('core.default_language_id', 1);
			}

			$this->em->remove($language);
			$this->em->flush();
			$this->em->commit();
		} catch (\Exception $e) {
			$this->em->rollback();
			throw $e;
		}

		return $this->redirectRoute('admin_langs');
	}

	public function editPhrasesAction($language_id, $group)
	{
		$vars = $this->getLangInfo($language_id);
		$vars['group'] = $group;

		$vars['lang_phrases'] = array('custom' => array(), 'original' => array());

		if ($group == 'CUSTOM') {
			$vars['lang_phrases']['custom'] = $this->em->getRepository('DeskPRO:Phrase')->getCustomPhrases($vars['language']);
			$groups = array();
			foreach ($vars['lang_phrases'] as $phrase) {
				$groups[] = $phrase->groupname;
			}
			$groups = array_unique($groups);
		} else {
			$groups = array($group);
		}

		$vars['master_phrases'] = array();
		if ($groups) {
			foreach ($groups as $g) {
				$groups_reader = new \Application\DeskPRO\ResourceScanner\LanguagePhrases();
				$master_phrases = $groups_reader->getGroupPhrases($g);
				$vars['master_phrases'] = array_merge($vars['master_phrases'], $master_phrases);
			}
			foreach ($groups as $g) {
				$groups_reader = new \Application\DeskPRO\ResourceScanner\LanguagePhrases(str_replace('%DP_ROOT%', DP_ROOT, $vars['language']->base_filepath));
				$master_phrases = $groups_reader->getGroupPhrases($g);
				$vars['lang_phrases']['original'] = array_merge($vars['master_phrases'], $master_phrases);
			}
		}

		// If we're in custom, only show the phrases we actually have
		if ($group == 'CUSTOM') {
			$set = array();
			foreach ($vars['lang_phrases'] as $phrase) {
				$set[$phrase->name] = isset($vars['master_phrases'][$phrase->name]) ? $vars['master_phrases'][$phrase->name] : null;
			}

			$vars['master_phrases'] = $set;
		}

		return $this->render('AdminBundle:Languages:lang-phrases.html.twig', $vars);
	}

	public function savePhrasesAction($language_id)
	{
		$language = $this->getLanguageOr404($language_id);

		$phrases = $this->in->getCleanValueArray('phrases', 'string', 'string');

		$phrase_reader = new \Application\DeskPRO\ResourceScanner\LanguagePhrases();

		$this->em->beginTransaction();
		try {
			foreach ($phrases as $phrase_id => $phrase_text) {
				$phrase = $this->em->getRepository('DeskPRO:Phrase')->getPhraseForLanguage($phrase_id, $language);
				if (!$phrase) {
					$phrase = new \Application\DeskPRO\Entity\Phrase();
					$phrase->language = $language;
					$phrase->name = $phrase_id;
					$master_phrase = $phrase_reader->getMasterPhrase($phrase_id);
					if (!$master_phrase) {
						$master_phrase = '';
					}
					$phrase->original_phrase = $master_phrase;
					$phrase->original_hash = $phrase_reader->generatePhraseHash($master_phrase);
				}

				if ($phrase_text == $phrase->original_phrase || !$phrase_text) {
					if ($phrase->id) {
						$this->em->remove($phrase);
					}
					continue;
				}

				$phrase->phrase = $phrase_text;

				$this->em->persist($phrase);
			}

			$this->em->flush();
			$this->em->commit();
		} catch (\Exception $e) {
			$this->em->rollback();
			throw $e;
		}

		return $this->createJsonResponse(array('success' => true));
	}

	############################################################################

	protected function getLangInfo($language_id)
	{
		if (is_object($language_id)) {
			$language = $language_id;
		} elseif ($language_id) {
			$language = $this->getLanguageOr404($language_id);
		} else {
			$language = new \Application\DeskPRO\Entity\Language();
		}

		$groups_reader = new \Application\DeskPRO\ResourceScanner\LanguagePhrases();

		$vars = array();
		$vars['language'] = $language;
		$phrase_groups = $groups_reader->getGroups();

		// Order so user, agent, admin
		$vars['phrase_groups'] = array(
			'user'  => $phrase_groups['user'],
			'agent' => $phrase_groups['agent'],
			'admin' => $phrase_groups['admin'],
		);

		return $vars;
	}

	/**
	 * @return \Application\DeskPRO\Entity\Language
	 */
	protected function getLanguageOr404($language_id)
	{
		$language = $this->em->find('DeskPRO:Language', $language_id);
		if (!$language) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no language with ID $language_id");
		}

		return $language;
	}
}
