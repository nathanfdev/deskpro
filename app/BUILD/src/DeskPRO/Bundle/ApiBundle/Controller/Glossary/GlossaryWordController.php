<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Glossary;

use Application\DeskPRO\Entity\GlossaryWord;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Glossary\GlossaryWordType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class GlossaryWordController.
 *
 * @ApiModes("all")
 * @Rest\Route("/glossary/words")
 * @ApiDoc(target="all", section="Glossary", output="Application\DeskPRO\Entity\GlossaryWord")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Glossary\GlossaryWordType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\GlossaryWord"
 *      }
 *     }
 * )
 */
class GlossaryWordController extends CrudController
{
    public static $entity    = GlossaryWord::class;
    public static $type      = GlossaryWordType::class;
    public static $listOrder = 'asc';

    /**
     * You can try to search the word and it's definition.
     *
     * @ApiDoc(
     *     description="Get a definition of the word",
     *     requirements={
     *         {
     *             "name"="word",
     *             "requirement"="\w+",
     *             "description"="The word",
     *             "dataType"="string"
     *         }
     *     },
     *     statusCodes={
     *         200="All looks good, we found what you want",
     *         404="Sorry we can find nothing with given parameters"
     *     },
     *     output="Application\DeskPRO\Entity\GlossaryWord"
     * )
     *
     * @Rest\Get("/{word}")
     *
     * @param string $word
     *
     * @return View
     */
    public function getByStringAction($word)
    {
        if (!$entity = $this->getRepository(self::$entity)->findOneBy(['word' => $word])) {
            throw $this->createNotFoundException();
        }

        return View::create($this->wrap($entity), Response::HTTP_OK);
    }
}
