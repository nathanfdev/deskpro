<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\PortalBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

        $response = $this->submitSavedForm($saved_form, $request);

        return $response;
    }
}
