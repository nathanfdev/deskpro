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

namespace DeskPRO\Bundle\ApiBundle\Controller\EmailTemplates;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PortalPageDisplay;
use Application\DeskPRO\Templating\Templates\TemplateSet;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiUnstable;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;

/**
 * API access to person settings.
 *
 * @ApiModes("all")
 * @Rest\Route("/email_templates/template")
 */
class TemplateController extends BaseController
{
    /**
     * @ApiDoc(
     *     section="Email Templates",
     *     description="Retrieve a template",
     *     requirements={
     *         {
     *             "name"="name",
     *             "description"="The template name",
     *             "dataType"="string"
     *         }
     *     },
     *)
     * @ApiUnstable()
     * @Rest\Get("/{name}")
     *
     * @param $name
     *
     * @return View
     */
    public function variablesAction($name)
    {
        if (strpos($name, 'EDIT_SIDEBAR_BLOCK:') === 0) {
            $block_id = substr($name, strlen('EDIT_SIDEBAR_BLOCK:'));
            $block    = $this->getManager()->getRepository(PortalPageDisplay::class)->find($block_id);
            if (!$block || !$block->getData('tpl')) {
                throw $this->createNotFoundException();
            }

            $name = $block->getData('tpl');
        }

        $set = $this->getTemplateSet();

        try {
            $template = $set->getTemplate($name);
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException();
        }

        $data = $set->exportTemplateToArray(
            $template,
            $this->get('translator'),
            !$this->get('settings_resolver')->getGlobalSettings()->get('core.enable_languages')
        );

        return new View($data);
    }

    /**
     * @return TemplateSet
     */
    private function getTemplateSet()
    {
        $set = new TemplateSet(
            $this->getManager(),
            $this->container->get('templating.email.twig')
        );

        return $set;
    }
}
