<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Controller\Emails;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiUnstable;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\SendmailBundle\Render\EmailRenderer;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

/**
 * API access to person settings.
 *
 * @ApiModes("all")
 */
class ViewModelController extends BaseController
{
    /**
     * @ApiDoc(
     *     section="Emails",
     *     description="list of a variables available for a view model",
     *     requirements={
     *         {
     *             "name"="className",
     *             "description"="The ViewModel class",
     *             "dataType"="string"
     *         }
     *     },
     *)
     * @ApiUnstable()
     * @Rest\Get("/emails/view_model/variables/{className}")
     *
     * @param $className
     *
     * @return View
     */
    public function variablesAction($className)
    {
        /** @var EmailRenderer $emailRenderer */
        $emailRenderer = $this->get('email.email_renderer');

        if (is_string($className)) {
            $className = 'DeskPRO\Bundle\SendmailBundle\View\Model\\'.$className;
        }

        return new View($emailRenderer->getStructure($className));
    }
}
