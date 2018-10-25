<?php

namespace DeskPRO\Bundle\MessengerBundle\Controller;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\MessengerBundle\Security\Authentication\MessengerAuthenticator;
use DeskPRO\Bundle\MessengerBundle\Serializer\Model\MessengerModelInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractMessengerController.
 */
abstract class AbstractMessengerController extends BaseController
{
    protected function wrap($data, array $meta = [])
    {
        if ($data instanceof MessengerModelInterface) {
            return $data->toArray();
        }

        return parent::wrap($data, $meta);
    }

    protected function getVisitorId(Request $request)
    {
        return $request->headers->get(MessengerAuthenticator::VISITOR_HEADER_NAME);
    }
}
