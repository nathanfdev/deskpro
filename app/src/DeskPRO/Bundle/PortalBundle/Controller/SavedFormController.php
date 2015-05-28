<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\PortalBundle\Controller;

use DeskPRO\Bundle\PortalBundle\SavedForm\SavedFormView;
use Orb\Util\Arrays;
use Orb\Util\Strings;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SavedFormController extends AbstractController
{
    /**
     * @Route("/saved-form/{auth_code}", name="saved_form_auto_submit", defaults={"auth_code":null})
     * @Security("is_granted('ROLE_USER')")
     */
    public function autoSubmitAction($auth_code = null, Request $request)
    {
        if ($auth_code) {
            $saved_form = $this->getFormSaver()->getByExternalCode($auth_code);
        } else {
            $saved_form = $this->getFormSaver()->getAutoSubmitSavedForm();
        }

        if (!$saved_form) {
            throw new NotFoundHttpException('this saved form does not exist, it may have expired');
        }
        $saved_form_view = new SavedFormView($saved_form);

        // prep the sub request to re-submit the form
        $csrf = Strings::random(10);
        $data = $saved_form->getFormData();
        $data = Arrays::replaceKeyWithValueRecursive($data, '_dp_csrf_token', $csrf);
        $url = $this->generateUrl($saved_form_view->getRouteName(), $saved_form_view->getRouteParams());
        $sub_request = Request::create(
            $url,
            'POST',
            $data,
            $request->cookies->all()
        );

        if (
            $saved_form->getPerson() !== $this->getUser() // email used in form not the same as this logged in user
            || $auth_code // if it was from a clicked link, must re-render
        ) {
            $sub_request->attributes->set('rerender-form', true); // force a re-render
        }
        $sub_request->setSession($request->getSession());
        $sub_request->cookies->set('_dp_csrf_token', $csrf);
        // end prep sub request

        // get rid of the saved form now
        $this->getFormSaver()->markCompleted($saved_form);

        // submit the form again for the user
        $response = $this->get('http_kernel')->handle($sub_request, HttpKernelInterface::SUB_REQUEST);

        return $response;
    }
}
