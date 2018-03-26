<?php

namespace DeskPROCloud\Bundle\CloudBillingBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;

class DemoExpiredController extends BaseController
{
    /**
     * @Route("expired_demo", name="expired_demo")
     *
     * @return Response
     */
    public function indexAction()
    {
        if (!defined('DPC_DEMO_EXPIRE') || !DPC_DEMO_EXPIRE) {
            return $this->redirectToRoute('agent');
        }

        return $this->render('CloudBillingBundle:DemoExpired:index.html.twig');
    }
}
