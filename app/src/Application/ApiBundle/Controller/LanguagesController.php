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

		$data['languages'] = $this->getApiData($langs);

		return $this->createApiResponse($data);
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