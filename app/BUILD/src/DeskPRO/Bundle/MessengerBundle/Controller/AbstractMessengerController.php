<?php

namespace DeskPRO\Bundle\MessengerBundle\Controller;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\MessengerBundle\Serializer\Model\MessengerModelInterface;

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
}
