<?php

namespace Application\DeskPRO\Controller;

class MiscController extends AbstractController
{
    public function emptyAction()
    {
        return $this->createResponse('');
    }

    public function notFoundAction()
    {
        throw $this->createNotFoundException();
    }

    public function goToBillingAction()
    {
        return $this->redirect($this->generateUrl('admin').'#/license');
    }

    public function goToReportsAction()
    {
        return $this->redirect($this->generateUrl('iface').'#/');
    }
}
