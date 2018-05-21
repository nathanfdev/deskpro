<?php

namespace DeskPRO\Bundle\PortalBundle\Controller\Api;

use DeskPRO\Bundle\AppBundle\Entity\Currency;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class CurrenciesController.
 *
 * @Rest\Route("/portal/api/currencies")
 */
class CurrenciesController extends AbstractApiController
{
    /**
     * @Rest\Get("")
     *
     * @return ApiWrapper
     */
    public function getCurrenciesAction()
    {
        return $this->wrap($this->getManager()->getRepository(Currency::class)->findAll());
    }
}
