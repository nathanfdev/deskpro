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
 * @subpackage AgentBundle
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
		$words = $this->em->getRepository('DeskPRO:GlossaryWord')->getWords();
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

		$this->em->persist($word);
		$this->em->flush();

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
		$word = $this->em->find('DeskPRO:GlossaryWord', $word_id);
		$word['content'] = $this->in->getString('content');

		$this->em->persist($word);
		$this->em->flush();

		return $this->createJsonResponse(array(
			'id' => $word['id'],
			'word' => $word['word'],
			'content' => $word['content'],
		));
	}

	public function glossaryDeleteWordJsonAction($word_id)
	{
		$word = $this->em->find('DeskPRO:GlossaryWord', $word_id);
		$this->em->remove($word);
		$this->em->flush();

		return $this->createJsonResponse(array(
			'id' => $word['id'],
			'word' => $word['word'],
		));
	}

	public function glossaryWordJsonAction($word_id)
	{
		$word = $this->em->find('DeskPRO:GlossaryWord', $word_id);

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
			$word = $this->em->getRepository('DeskPRO:GlossaryWord')->findOneByWord($word);
			$def = $word['content'];
		} catch (\Exception $e) {
			$def = '';
		}

		return $this->createResponse($def);
	}
}
