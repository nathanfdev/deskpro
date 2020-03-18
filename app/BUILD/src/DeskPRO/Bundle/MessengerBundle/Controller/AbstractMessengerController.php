<?php

namespace DeskPRO\Bundle\MessengerBundle\Controller;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\MessengerBundle\Security\EventListener\VisitorIdListener;
use DeskPRO\Bundle\MessengerBundle\Serializer\Model\MessengerModelInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractMessengerController.
 */
abstract class AbstractMessengerController extends BaseController
{
    /**
     * @param mixed $data
     * @param array $meta
     *
     * @return array|\DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper
     */
    protected function wrap($data, array $meta = [])
    {
        if ($data instanceof MessengerModelInterface) {
            return $data->toArray();
        }

        return parent::wrap($data, $meta);
    }

    /**
     * @param Request $request
     *
     * @return string|string[]|null
     */
    protected function getVisitorId(Request $request)
    {
        return $request->headers->get(VisitorIdListener::VISITOR_HEADER_NAME);
    }

    /**
     * @param Request $request
     *
     * @return \Application\DeskPRO\Entity\Person|void|null
     */
    protected function getJwtUser(Request $request)
    {
        if ($request->headers->has('X-JWT-TOKEN')) {
            return $this->container->get('widget_jwt_decoder')->getPersonFromJwtPayload($request->headers->get('X-JWT-TOKEN'));
        }

        return;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->getJwtUser($this->container->get('request')) ?: parent::getUser();
    }
}
