<?php

namespace DeskPRO\Bundle\ApiBundle\Controller;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\Currency;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class CurrenciesController.
 *
 * @ApiModes("all")
 * @Rest\Route("/currencies")
 * @ApiDoc(target="all", section="Currencies", output="DeskPRO\Bundle\AppBundle\Entity\Currency")
 */
class CurrenciesController extends CrudController
{
    public static $entity     = Currency::class;
    public static $exposeOnly = ['get', 'list', 'count'];
}
