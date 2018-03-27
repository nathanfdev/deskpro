<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Controller;

class DataController extends AbstractController
{
    public function interfaceDataAction()
    {
        $r = $this->createResponse('', 200);
        $r->headers->set('Content-Type', 'application/javascript');

        return $r;
    }

    public function logJsErrorAction()
    {
        return $this->createJsonResponse([
            'logged' => true,
        ]);
    }
}
