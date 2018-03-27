<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\EmailTemplates;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiUnstable;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\SendmailBundle\Render\EmailRenderer;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

/**
 * API access to person settings.
 *
 * @ApiModes("all")
 * @Feature("email_templates")
 * @Rest\Route("/email_templates/view_model")
 */
class ViewModelController extends BaseController
{
    /**
     * @ApiDoc(
     *     section="Email Templates",
     *     description="list of a variables available for a view model",
     *     requirements={
     *         {
     *             "name"="className",
     *             "description"="The ViewModel class",
     *             "dataType"="string"
     *         }
     *     },
     *     output="array"
     *)
     * @ApiUnstable()
     * @Rest\Get("/variables/{className}")
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
