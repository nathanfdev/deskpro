<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\ApiBundle\Controller\Glossary;

use Application\DeskPRO\Entity\GlossaryWord;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Glossary\GlossaryWordType;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class GlossaryWordController.
 *
 * @ApiModes("all")
 * @Route("/glossary/words")
 */
class GlossaryWordController extends CrudController
{
    public static $entity = GlossaryWord::class;
    public static $type   = GlossaryWordType::class;

    /**
     * @ApiDoc(
     *      description="Get a definition of the word",
     *      requirements={
     *          {
     *              "name"="word",
     *              "requirement"="\w+",
     *              "description"="The word",
     *              "dataType"="string"
     *          }
     *      },
     *      statusCodes={
     *          200="Success",
     *          403="Denied",
     *          404="Not Found"
     *      }
     * )
     * @Get("/{word}")
     */
    public function getByStringAction($word)
    {
        if (!$entity = $this->getRepository(self::$entity)->findOneBy(['word' => $word])) {
            throw $this->createNotFoundException();
        }

        return View::create($this->dataSerialize($entity), Response::HTTP_OK);
    }
}
