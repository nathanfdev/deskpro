<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\GlossaryWordDefinition;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;

/**
 * SWG\Resource(
 *    resourcePath="/glossary",
 *    description="Operations about Glossary Words",
 *    basePath="/api"
 * ).
 *
 * @ApiModes("all")
 */
class GlossaryController extends AbstractController
{
    /**
     * SWG\Api(
     *    path="/glossary",
     *    SWG\Operation(
     *        method="GET",
     *        summary="List glossary words.",
     *        notes="Returns list of words that matched.",
     *        type="array",
     *        SWG\Parameters (
     *            SWG\Parameter(
     *                name="word",
     *                description="If specified, gets words containing this string.",
     *                paramType="query",
     *                required=false,
     *                type="string"
     *            )
     *        )
     *    )
     * ).
     */
    public function listAction()
    {
        $word = $this->in->getString('word');
        if ($word) {
            $words = $this->em->getRepository('DeskPRO:GlossaryWord')->getWordsContaining($word);
        } else {
            $words = $this->em->getRepository('DeskPRO:GlossaryWord')->getWords();
        }

        return $this->createApiResponse(['words' => $words]);
    }

    /**
     * SWG\Api(
     *    path="/glossary/lookup",
     *    SWG\Operation(
     *        method="GET",
     *        summary="Looks up a specific glossary word.",
     *        notes="Information about the word, if in the glossary.",
     *        type="GlossaryWord",
     *        SWG\Parameters (
     *            SWG\Parameter(
     *                name="word",
     *                description="If specified, gets this word.",
     *                paramType="query",
     *                required=true,
     *                type="string"
     *            )
     *        )
     *    )
     * ).
     */
    public function lookupAction()
    {
        $word = $this->in->getString('word');

        $word = $this->em->getRepository('DeskPRO:GlossaryWord')->findOneByWord($word);
        if ($word) {
            return $this->createApiResponse(['word' => $word->toApiData()]);
        } else {
            return $this->createApiResponse(['word' => false]);
        }
    }

    /**
     * SWG\Api(
     *    path="/glossary",
     *    SWG\Operation(
     *        method="POST",
     *        summary="Add a glossary word.",
     *        SWG\Parameters (
     *            SWG\Parameter(
     *                name="definition",
     *                description="Definition of given words.",
     *                paramType="query",
     *                required=true,
     *                type="string"
     *            ),
     *            SWG\Parameter(
     *                name="word[]",
     *                description="Comma seperated list of words to associate with this definition.",
     *                paramType="query",
     *                required=true,
     *                type="string"
     *            ),
     *            SWG\Parameter(
     *                name="brand_id",
     *                description="Optional id of the brand to add the word to.",
     *                paramType="query",
     *                required=false,
     *                type="integer"
     *            )
     *        )
     *    )
     * ).
     */
    public function newWordAction()
    {
        $def = new GlossaryWordDefinition();
        $def->setDefinition($this->in->getString('definition'));

        $brandId = $this->in->getUInt('brand_id');
        if (!$brandId) {
            $brandId = $this->get('settings_resolver')->getGlobalSettings()->get('portal.default_brand');
        }
        /** @var Brand $brand */
        $brand = $this->em->getRepository(Brand::class)->find($brandId);
        if (!$brand) {
            return $this->createApiErrorResponse('invalid_argument.brand_id', 'Unknown brand');
        }

        $words = [];
        foreach ($this->in->getCleanValueArray('word', 'string') as $word) {
            $words[] = $def->addNewWord($word, $brand);
        }

        if (!count($def->getWords())) {
            return $this->createApiErrorResponse('invalid_argument.word', 'words already exist or not provided');
        }

        $this->em->persist($def);
        $this->em->flush();

        $ids = [];
        foreach ($words as $word) {
            $ids[] = $word->id;
        }

        return $this->createApiResponse(['ids' => $ids, 'definition_id' => $def->getId()]);
    }

    /**
     * SWG\Api(
     *    path="/glossary/{word_id}",
     *    SWG\Operation(
     *        method="GET",
     *        summary="Gets a glossary word by word ID.",
     *        notes="Information about the word by word ID, if in the glossary.",
     *        type="GlossaryWord",
     *        SWG\Parameters (
     *            SWG\Parameter(
     *                name="word_id",
     *                description="ID of the word that needs to be searched.",
     *                paramType="path",
     *                required=true,
     *                type="string"
     *            )
     *        ),
     *        SWG\ResponseMessage(code=404, message="Glossary word not found")
     *    )
     * ).
     *
     * @param $word_id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getWordAction($word_id)
    {
        $word = $this->_getWordOr404($word_id);

        return $this->createApiResponse(['word' => $word->toApiData()]);
    }

    /**
     * SWG\Api(
     *    path="/glossary/{word_id}",
     *    SWG\Operation(
     *        method="DELETE",
     *        summary="Deletes a glossary word by ID.",
     *        SWG\Parameters (
     *            SWG\Parameter(
     *                name="word_id",
     *                description="ID of the word that needs to be deleted.",
     *                paramType="path",
     *                required=true,
     *                type="integer"
     *            )
     *        ),
     *        SWG\ResponseMessage(code=404, message="Glossary word not found")
     *    )
     * ).
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
     * SWG\Api(
     *    path="/glossary/definitions/{definition_id}",
     *    SWG\Operation(
     *        method="GET",
     *        summary="Gets a glossary word definition.",
     *        notes="Information about the Glossary Word definition by definition ID, if in the glossary.",
     *        type="GlossaryWordDefinition",
     *        SWG\Parameters (
     *            SWG\Parameter(
     *                name="definition_id",
     *                description="ID of the word defination that needs to be searched.",
     *                paramType="path",
     *                required=true,
     *                type="string"
     *            )
     *        ),
     *        SWG\ResponseMessage(code=404, message="Glossary definition not found")
     *    )
     * ).
     */
    public function getDefinitionAction($definition_id)
    {
        $def = $this->_getDefinitionOr404($definition_id);

        return $this->createApiResponse(['definition' => $def->toApiData()]);
    }

    /**
     * SWG\Api(
     *    path="/glossary/definitions/{definition_id}",
     *    SWG\Operation(
     *        method="POST",
     *        summary="Updates a glossary word definition.",
     *        SWG\Parameters (
     *            SWG\Parameter(
     *                name="definition_id",
     *                description="ID of the word defination that needs to be updated.",
     *                paramType="path",
     *                required=true,
     *                type="string"
     *            ),
     *            SWG\Parameter(
     *                name="brand_id",
     *                description="Optional id of the brand to add the word to.",
     *                paramType="query",
     *                required=false,
     *                type="integer"
     *            )
     *        ),
     *        SWG\ResponseMessage(code=404, message="Glossary definition not found")
     *    )
     * ).
     *
     * @param $definition_id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function postDefinitionAction($definition_id)
    {
        $def = $this->_getDefinitionOr404($definition_id);

        $brandId = $this->in->getUInt('brand_id');
        if (!$brandId) {
            $brandId = $this->get('settings_resolver')->getGlobalSettings()->get('portal.default_brand');
        }
        /** @var Brand $brand */
        $brand = $this->em->getRepository(Brand::class)->find($brandId);
        if (!$brand) {
            return $this->createApiErrorResponse('invalid_argument.brand_id', 'Unknown brand');
        }

        if ($this->in->checkIsset('definition')) {
            $def->setDefinition($this->in->getString('definition'));
        }

        foreach ($this->in->getCleanValueArray('word', 'string') as $word) {
            $def->addNewWord($word, $brand);
        }

        $this->em->persist($def);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * SWG\Api(
     *    path="/glossary/definitions/{definition_id}",
     *    SWG\Operation(
     *        method="DELETE",
     *        summary="Deletes a glossary word definition.",
     *        SWG\Parameters (
     *            SWG\Parameter(
     *                name="definition_id",
     *                description="ID of the word defination that needs to be deleted.",
     *                paramType="path",
     *                required=true,
     *                type="string"
     *            )
     *        ),
     *        SWG\ResponseMessage(code=404, message="Glossary definition not found")
     *    )
     * ).
     */
    public function deleteDefinitionAction($definition_id)
    {
        $def = $this->_getDefinitionOr404($definition_id);

        $this->em->remove($def);
        $this->em->flush();

        return $this->createSuccessResponse();
    }

    /**
     * @param int $id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return \Application\DeskPRO\Entity\GlossaryWord
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
     * @param int $id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return \Application\DeskPRO\Entity\GlossaryWordDefinition
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
