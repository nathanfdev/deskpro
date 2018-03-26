<?php

namespace DeskPRO\Bundle\PortalBundle\Controller;

use DeskPRO\Bundle\PortalBundle\EventListener\RedirectProtectionListener;
use DpSys\CodePlugin\DpPlugins;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class RedirectController.
 */
class RedirectController extends AbstractController
{
    const GOTO_VENDOR_HOME = 'vendor-home';

    /**
     * @Route("/billing")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function goToBillingAction()
    {
        return $this->redirect($this->generateUrl('admin').'#/license');
    }

    /**
     * @Route("/goto/{id}", name="goto")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function goToAction($id)
    {
        $response = DpPlugins::getManager()->handleGoRequest($id, $this->getContainer());
        if ($response) {
            return $response;
        }

        switch ($id) {
            case self::GOTO_VENDOR_HOME:
                return new RedirectResponse('https://www.deskpro.com/', 302, [RedirectProtectionListener::ALLOW_REDIRECT_OFFSITE_HEADER => '1']);
        }

        throw $this->createNotFoundException();
    }
}
