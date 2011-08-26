<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\GlossaryWord;

use Orb\Util\Strings;
use Orb\Util\Arrays;
use Orb\Util\Util;

/**
 * Glossary listing and editing
 */
class GlossaryController extends AbstractController
{
	public function glossaryListAction()
	{
		$words = App::getEntityRepository('DeskPRO:GlossaryWord')->getWords();
		$word_count = count($words);
		$words = Arrays::sortIntoAlphabeticalIndex($words, null, true, true);

		return $this->render('AgentBundle:Glossary:list-glossary.html.twig', array(
			'words'      => $words,
			'word_count' => $word_count,
		));
	}

	public function glossaryNewWordJsonAction()
	{
		$word = new GlossaryWord();
		$word['word'] = $this->in->getString('word');
		$word['content'] = $this->in->getString('content');

		App::getOrm()->persist($word);
		App::getOrm()->flush();

		$first = Strings::utf8_substr($word['word'], 0, 1);
		$first = Strings::utf8_accents_to_ascii($first);
		$first = Strings::utf8_strtoupper($first);

		if (is_numeric($first)) {
			$first = '#';
		} elseif (!preg_match('#[A-Z]#', $first)) {
			$first = '@';
		}

		return $this->createJsonResponse(array(
			'id' => $word['id'],
			'word' => $word['word'],
			'content' => $word['content'],
			'letter' => $first
		));
	}

	public function glossarySaveWordJsonAction($word_id)
	{
		$word = App::findEntity('DeskPRO:GlossaryWord', $word_id);
		$word['content'] = $this->in->getString('content');

		App::getOrm()->persist($word);
		App::getOrm()->flush();

		return $this->createJsonResponse(array(
			'id' => $word['id'],
			'word' => $word['word'],
			'content' => $word['content'],
		));
	}

	public function glossaryDeleteWordJsonAction($word_id)
	{
		$word = App::findEntity('DeskPRO:GlossaryWord', $word_id);
		App::getOrm()->remove($word);
		App::getOrm()->flush();

		return $this->createJsonResponse(array(
			'id' => $word['id'],
			'word' => $word['word'],
		));
	}

	public function glossaryWordJsonAction($word_id)
	{
		$word = App::findEntity('DeskPRO:GlossaryWord', $word_id);

		return $this->createJsonResponse(array(
			'id' => $word['id'],
			'word' => $word['word'],
			'content' => $word['content']
		));
	}

	public function tipAction($word)
	{
		$def = '';

		try {
			$word = App::getEntityRepository('DeskPRO:GlossaryWord')->findOneByWord($word);
			$def = $word['content'];
		} catch (\Exception $e) {
			$def = '';
		}

		return $this->createResponse($def);
	}
}
