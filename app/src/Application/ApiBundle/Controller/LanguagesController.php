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
 * @subpackage ApiBundle
 */

namespace Application\ApiBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Exception\ValidationException;
use Application\DeskPRO\Languages\LangPackInfo;
use Orb\Util\Arrays;
use Orb\Util\Numbers;

class LanguagesController extends AbstractController
{
	####################################################################################################################
	# list
	####################################################################################################################

	public function listAction()
	{
		$langs = $this->em->createQuery("
			SELECT l
			FROM DeskPRO:Language l
			ORDER BY l.title ASC
		")->execute();

		$installed_packs = array();
		foreach ($langs as $l) $installed_packs[$l->getSysName()] = $l->getSysName();

		$langpacks = new \Application\DeskPRO\Languages\LangPackInfo();
		$pack_titles = $langpacks->getLangTitles();
		$pack_local_titles = $langpacks->getLangTitles(true);

		foreach ($pack_titles as $id => $title) {
			$flag = $langpacks->getLangInfo($id, 'flag_image');
			$r = array(
				'id'           => $id,
				'title'        => $title,
				'local_title'  => $pack_local_titles[$id],
				'flag'         => $flag,
				'is_installed' => isset($installed_packs[$id])
			);

			$all_packs[] = $r;
		}

		$data['languages']       = $this->getApiData($langs);
		$data['packs']           = $all_packs;
		$data['default_lang_id'] = $this->container->getLanguageData()->getDefaultId();
		$data['is_multi_lang']   = $this->container->getLanguageData()->isMultiLang();

		return $this->createApiResponse($data);
	}


	####################################################################################################################
	# get-lang
	####################################################################################################################

	public function getLangAction($id)
	{
		$langpacks = new LangPackInfo();

		if (Numbers::isInteger($id)) {
			$lang = $this->container->getLanguageData()->get($id);
			if (!$lang) {
				return $this->createNotFoundException();
			}

			$lang_info = $langpacks->getLangInfo($lang->sys_name);
		} else {
			if (!$langpacks->hasLang($id)) {
				return $this->createNotFoundException();
			}

			$lang_info = $langpacks->getLangInfo($id);
			$lang = null;

			foreach ($this->container->getLanguageData()->getAll() as $l) {
				if ($l->sys_name == $lang_info['id']) {
					$lang = $l;
					break;
				}
			}
		}

		if ($lang) {
			$lang_info['is_installed'] = true;
		}

		return $this->createApiResponse(array(
			'pack' => $lang_info,
			'language' => $lang ? $lang->toApiData() : null,
		));
	}


	####################################################################################################################
	# get-phrase
	####################################################################################################################

	public function getPhraseAction($phrase_id, $for_lang = -1)
	{
		if ($for_lang != -1) {
			$lang = $this->em->find('DeskPRO:Language', $for_lang);
			if (!$lang) {
				throw ValidationException::create("for_lang.invalid", "Invalid lanugage specified");
			}

			$phrase = $this->em->getRepository('DeskPRO:Phrase')->getPhraseForLanguage($phrase_id, $lang);

			if ($phrase) {
				$data['phrase'] = $phrase->toApiData(false);
			} else {
				$data['phrase'] = null;
			}

		} else {
			$langs = $this->em->createQuery("
				SELECT l
				FROM DeskPRO:Language l
				ORDER BY l.title ASC
			")->execute();

			$data['lang_phrases'] = array();
			foreach ($langs as $lang) {
				$phrase = $this->em->getRepository('DeskPRO:Phrase')->getPhraseForLanguage($phrase_id, $lang);

				if ($phrase) {
					$data['lang_phrases'][] = $phrase->toApiData(true);
				}
			}
		}

		return $this->createApiResponse($data);
	}

	####################################################################################################################
	# install-lang
	####################################################################################################################

	public function installLangAction($id)
	{
		$langpacks = new LangPackInfo();

		if (!$langpacks->hasLang($id)) {
			return $this->createNotFoundException();
		}

		$lang_info = $langpacks->getLangInfo($id);

		foreach ($this->container->getLanguageData()->getAll() as $l) {
			if ($l->sys_name == $lang_info['id']) {
				return $this->createApiErrorResponse('exists', "$id is already installed");
			}
		}

		$lang = $langpacks->newLanguageEntity($id);

		$this->db->beginTransaction();
		try {
			$this->em->persist($lang);
			$this->em->flush();
			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
		}

		return $this->createApiCreateResponse(array(
			'pack_id' => $id,
			'language_id' => $lang->id
		), $this->generateUrl('api_langs_getinfo', array('id' => $lang->id)));
	}


	####################################################################################################################
	# uninstall-lang
	####################################################################################################################

	public function uninstallLangAction($id)
	{
		$langpacks = new LangPackInfo();

		if (Numbers::isInteger($id)) {
			$lang = $this->container->getLanguageData()->get($id);
			if (!$lang) {
				return $this->createNotFoundException();
			}

			$lang_info = $langpacks->getLangInfo($lang->sys_name);

		} else {
			if (!$langpacks->hasLang($id)) {
				return $this->createNotFoundException();
			}

			$lang_info = $langpacks->getLangInfo($id);
			$lang = null;

			foreach ($this->container->getLanguageData()->getAll() as $l) {
				if ($l->sys_name == $lang_info['id']) {
					$lang = $l;
					break;
				}
			}

			if (!$lang) {
				return $this->createNotFoundException();
			}
		}

		$default_id = $this->container->getLanguageData()->getDefaultId();
		if ($default_id == $lang->id) {
			return $this->createApiErrorResponse('no_delete_default', 'You cannot delete the default language');
		}

		$old_lang_id = $lang->id;

		$this->em->remove($lang);
		$this->em->flush();

		return $this->createSuccessResponse(array(
			'old_pack_id'    => $lang_info['id'],
			'old_language_id'=> $old_lang_id
		));
	}


	####################################################################################################################
	# save-phrase
	####################################################################################################################

	public function savePhraseAction($phrase_id)
	{
		$langs = $this->em->createQuery("
			SELECT l
			FROM DeskPRO:Language l INDEX BY l.id
			ORDER BY l.title ASC
		")->execute();

		foreach ($this->in->getArrayValue('lang_phrases') as $lang_phrase) {
			$phrase_text = trim($lang_phrase['phrase']);
			$lang_id     = intval($lang_phrase['language_id']);

			if (!isset($langs[$lang_id])) {
				continue;
			}

			$lang = $langs[$lang_id];

			$phrase = $this->em->getRepository('DeskPRO:Phrase')->getPhraseForLanguage($phrase_id, $lang);
			if (!$phrase_text) {
				if ($phrase) {
					$this->em->remove($phrase);
				}
			} else {
				if (!$phrase) {
					$phrase = new \Application\DeskPRO\Entity\Phrase();
					$phrase->language = $lang;
					$phrase->name = $phrase_id;
					$phrase->original_phrase = '';
					$phrase->original_hash = md5(null);
				}

				$phrase->phrase = $phrase_text;
				$this->em->persist($phrase);
			}
		}

		$this->em->flush();

		return $this->createSuccessResponse();
	}
}