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
		return $this->render('AdminBundle:Languages:install.html.twig');
	}

	public function installUploadAction()
	{
		/** @var $file UploadedFile */
		$file = $this->request->files->get('upfile');

		if (!$file || !$file->isValid()) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$pack_file = LanguagePackFile::newFromFile($file->getRealPath());

		$lang_installer = new LanguageInstaller($this->em);
		$lang = $lang_installer->installFromPackFile($pack_file);

		return $this->redirectRoute('admin_langs_editlang', array('language_id' => $lang->getId()));
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

		if ($group == 'CUSTOM') {
			$vars['lang_phrases'] = $this->em->getRepository('DeskPRO:Phrase')->getCustomPhrases($vars['language']);
		} else {
			$vars['lang_phrases'] = $this->em->getRepository('DeskPRO:Phrase')->getLanguagePhrasesInGroup($vars['language'], $group);
		}

		$groups = array();
		foreach ($vars['lang_phrases'] as $phrase) {
			$groups[] = $phrase->groupname;
		}
		$groups = array_unique($groups);

		$vars['master_phrases'] = array();
		if ($groups) {
			foreach ($groups as $g) {
				$groups_reader = new \Application\DeskPRO\ResourceScanner\LanguagePhrases();
				$master_phrases = $groups_reader->getGroupPhrases($g);
				$vars['master_phrases'] = array_merge($vars['master_phrases'], $master_phrases);
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
	 * @return Application\DeskPRO\Entity\Language
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
