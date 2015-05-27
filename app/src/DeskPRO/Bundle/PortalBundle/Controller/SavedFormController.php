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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SavedFormController extends AbstractController
{
    /**
     * @Route("/saved-form/submit/{auth_code}", name="saved_form_auto_submit", defaults={"auth_code":null})
     */
    public function autoSubmitAction($auth_code = null)
    {
        if ($auth_code) {
            $saved_form = $this->getFormSaver()->getByExternalCode($auth_code);
        } else {
            $saved_form = $this->getFormSaver()->getAutoSubmitSavedForm();
        }

        if (!$saved_form) {
            throw new NotFoundHttpException('this saved form does not exist, it may have expired');
        }

        $rendered_response = $this->render(
            'PortalBundle:SavedForm:auto_submit.html.twig',
            array(
                'saved_form' => new SavedFormView($saved_form)
            )
        );

        // need to remove this now (from session and DB) so we dont continue to display it, or accidentally
        // perform the action again.
        $this->getFormSaver()->markCompleted($saved_form);

        return $rendered_response;
    }
}
