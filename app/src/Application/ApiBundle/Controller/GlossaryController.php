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

/**
* @SWG\Resource(
* 	resourcePath="/glossary",
* 	description="Operations about Glossary Words",
* 	basePath="/api"
* )
*/
class GlossaryController extends AbstractController
{
	/**
	 * @SWG\Api(
	 * 	path="/glossary",
	 * 	@SWG\Operation(
	 * 		method="GET",
	 * 		summary="List glossary words.",
	 * 		notes="Returns list of words that matched.",
	 *		type="array",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="word",
	 *				description="If specified, gets words containing this string.",
	 *				paramType="query",
	 *				required=false,
	 *				type="string"
	 *			)
	 *		)
	 * 	)
	 * )
	 */
	public function listAction()
	{
		$word = $this->in->getString('word');
		if ($word) {
			$words = $this->em->getRepository('DeskPRO:GlossaryWord')->getWordsContaining($word);
		} else {
			$words = $this->em->getRepository('DeskPRO:GlossaryWord')->getWords();
		}

		return $this->createApiResponse(array('words' => $words));
	}

	/**
	 * @SWG\Api(
	 * 	path="/glossary/lookup",
	 * 	@SWG\Operation(
	 * 		method="GET",
	 * 		summary="Looks up a specific glossary word.",
	 * 		notes="Information about the word, if in the glossary.",
	 *		type="GlossaryWord",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="word",
	 *				description="If specified, gets this word.",
	 *				paramType="query",
	 *				required=true,
	 *				type="string"
	 *			)
	 *		)
	 * 	)
	 * )
	 */
	public function lookupAction()
	{
		$word = $this->in->getString('word');

		$word = $this->em->getRepository('DeskPRO:GlossaryWord')->findOneByWord($word);
		if ($word) {
			return $this->createApiResponse(array('word' => $word->toApiData()));
		} else {
			return $this->createApiResponse(array('word' => false));
		}
	}

	/**
	 * @SWG\Api(
	 * 	path="/glossary",
	 * 	@SWG\Operation(
	 * 		method="POST",
	 * 		summary="Add a glossary word.",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="definition",
	 *				description="Definition of given words.",
	 *				paramType="query",
	 *				required=true,
	 *				type="string"
	 *			),
	 *			@SWG\Parameter(
	 *				name="word[]",
	 *				description="Comma seperated list of words to associate with this definition.",
	 *				paramType="query",
	 *				required=true,
	 *				type="string"
	 *			)
	 *		)
	 * 	)
	 * )
	 */
	public function newWordAction()
	{
		$def = new \Application\DeskPRO\Entity\GlossaryWordDefinition();
		$def->definition = $this->in->getString('definition');

		$words = array();
		foreach ($this->in->getCleanValueArray('word', 'string') AS $word) {
			$words[] = $def->addWord($word);
		}

		if (!count($def->words)) {
			return $this->createApiErrorResponse('invalid_argument.word', 'words already exist or not provided');
		}

		$this->em->persist($def);
		$this->em->flush();

		$ids = array();
		foreach ($words AS $word) {
			$ids[] = $word->id;
		}

		return $this->createApiResponse(array('ids' => $ids, 'definition_id' => $def->id));
	}

	/**
	 * @SWG\Api(
	 * 	path="/glossary/{word_id}",
	 * 	@SWG\Operation(
	 * 		method="GET",
	 * 		summary="Gets a glossary word by word ID.",
	 * 		notes="Information about the word by word ID, if in the glossary.",
	 *		type="GlossaryWord",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="word_id",
	 *				description="ID of the word that needs to be searched.",
	 *				paramType="path",
	 *				required=true,
	 *				type="string"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Glossary word not found")
	 * 	)
	 * )
	 */
	public function getWordAction($word_id)
	{
		$word = $this->_getWordOr404($word_id);

		return $this->createApiResponse(array('word' => $word->toApiData()));
	}

	/**
	 * @SWG\Api(
	 * 	path="/glossary/{word_id}",
	 * 	@SWG\Operation(
	 * 		method="DELETE",
	 * 		summary="Deletes a glossary word by ID.",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="word_id",
	 *				description="ID of the word that needs to be deleted.",
	 *				paramType="path",
	 *				required=true,
	 *				type="integer"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Glossary word not found")
	 * 	)
	 * )
	 */
	public function deleteWordAction($word_id)
	{
		$word = $this->_getWordOr404($word_id);

		if (count($word->definition->words) == 1) {
			$this->em->remove($word->definition);
		} else {
			$this->em->remove($word);
		}
		$this->em->flush();

		return $this->createSuccessResponse();
	}

	/**
	 * @SWG\Api(
	 * 	path="/glossary/definitions/{definition_id}",
	 * 	@SWG\Operation(
	 * 		method="GET",
	 * 		summary="Gets a glossary word definition.",
	 * 		notes="Information about the Glossary Word definition by definition ID, if in the glossary.",
	 *		type="GlossaryWordDefinition",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="definition_id",
	 *				description="ID of the word defination that needs to be searched.",
	 *				paramType="path",
	 *				required=true,
	 *				type="string"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Glossary definition not found")
	 * 	)
	 * )
	 */
	public function getDefinitionAction($definition_id)
	{
		$def = $this->_getDefinitionOr404($definition_id);

		return $this->createApiResponse(array('definition' => $def->toApiData()));
	}

	/**
	 * @SWG\Api(
	 * 	path="/glossary/definitions/{definition_id}",
	 * 	@SWG\Operation(
	 * 		method="POST",
	 * 		summary="Updates a glossary word definition.",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="definition_id",
	 *				description="ID of the word defination that needs to be updated.",
	 *				paramType="path",
	 *				required=true,
	 *				type="string"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Glossary definition not found")
	 * 	)
	 * )
	 */
	public function postDefinitionAction($definition_id)
	{
		$def = $this->_getDefinitionOr404($definition_id);

		if ($this->in->checkIsset('definition')) {
			$def->definition = $this->in->getString('definition');
		}

		foreach ($this->in->getCleanValueArray('word', 'string') AS $word) {
			$def->addWord($word);
		}

		$this->em->persist($def);
		$this->em->flush();

		return $this->createSuccessResponse();
	}

	/**
	 * @SWG\Api(
	 * 	path="/glossary/definitions/{definition_id}",
	 * 	@SWG\Operation(
	 * 		method="DELETE",
	 * 		summary="Deletes a glossary word definition.",
	 *		@SWG\Parameters (
	 *			@SWG\Parameter(
	 *				name="definition_id",
	 *				description="ID of the word defination that needs to be deleted.",
	 *				paramType="path",
	 *				required=true,
	 *				type="string"
	 *			)
	 *		),
	 *		@SWG\ResponseMessage(code=404, message="Glossary definition not found")
	 * 	)
	 * )
	 */
	public function deleteDefinitionAction($definition_id)
	{
		$def = $this->_getDefinitionOr404($definition_id);

		$this->em->remove($def);
		$this->em->flush();

		return $this->createSuccessResponse();
	}

	/**
	 * @param integer $id
	 * @return \Application\DeskPRO\Entity\GlossaryWord
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	protected function _getWordOr404($id)
	{
		$word = $this->em->getRepository('DeskPRO:GlossaryWord')->findOneById($id);

		if (!$word) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no word with ID $id");
		}

		return $word;
	}

	/**
	 * @param integer $id
	 * @return \Application\DeskPRO\Entity\GlossaryWordDefinition
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	protected function _getDefinitionOr404($id)
	{
		$def = $this->em->getRepository('DeskPRO:GlossaryWordDefinition')->findOneById($id);

		if (!$def) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no definition with ID $id");
		}

		return $def;
	}
}
