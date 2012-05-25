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

class LanguagesController extends AbstractController
{
	############################################################################
	# list
	############################################################################

	public function indexAction()
    {
		$languages = $this->em->getRepository('DeskPRO:Language')->findAll();

        return $this->render('AdminBundle:Languages:index.html.twig', array(
			'languages' => $languages,
		));
	}

	############################################################################
	# new-language
	############################################################################

	public function newLanguageAction()
	{
		$packs_reader = new \Application\DeskPRO\ResourceScanner\LanguagePacks();
		$packs = $packs_reader->getPacks();

		return $this->render('AdminBundle:Languages:new-lang.html.twig', array(
			'packs' => $packs,
		));
	}

	public function newLanguageSaveAction()
	{
		$packs_reader = new \Application\DeskPRO\ResourceScanner\LanguagePacks();
		$packs = $packs_reader->getPacks();

		$pack = $this->in->getString('language_package');
		if (!isset($packs[$pack])) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$language = new \Application\DeskPRO\Entity\Language();
		$language->language_package = $pack;
		$language->title = $pack::getTitle();
		$language->locale = $pack::getLocale();

		$this->em->transactional(function($em) use ($language) {
			$em->persist($language);
			$em->flush();
		});

		return $this->redirectRoute('admin_langs_editlang', array('language_id' => $language->id));
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

		$packs_reader = new \Application\DeskPRO\ResourceScanner\LanguagePacks();
		$vars['packs'] = $packs_reader->getPacks();

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

		$groups_reader = new \Application\DeskPRO\ResourceScanner\LanguagePhrases();
		$master_phrases = $groups_reader->getGroupPhrases($group);
		$vars['master_phrases'] = $master_phrases;

		$class = $vars['language']->language_package;
		if (class_exists($class, true));
		$path = $class::getLangPath();


		$groups_reader = new \Application\DeskPRO\ResourceScanner\LanguagePhrases($path);
		$lang_phrases = $groups_reader->getGroupPhrases($group);
		$vars['lang_phrases'] = $lang_phrases;

		$custom_phrases = $this->em->getRepository('DeskPRO:Phrase')->getPhrasesInGroup($vars['language'], $group);
		$vars['custom_phrases'] = $custom_phrases;

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
				$phrase = $this->em->getRepository('DeskPRO:Phrase')->getPhraseForLanguage($language, $phrase_id);
				if (!$phrase) {
					$phrase = new \Application\DeskPRO\Entity\Phrase();
					$phrase->language = $language;
					$phrase->name = $phrase_id;
				}

				$master_phrase = $phrase_reader->getMasterPhrase($phrase_id);

				if ($phrase_text == $master_phrase || !$phrase_text) {
					if ($phrase->id) {
						$this->em->remove($phrase);
					}
					continue;
				}

				$phrase->phrase = $phrase_text;
				$phrase->original_hash = $phrase_reader->generatePhraseHash($master_phrase);
				$phrase->is_outdated = false;

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
		$vars['phrase_groups'] = $groups_reader->getGroups();

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
