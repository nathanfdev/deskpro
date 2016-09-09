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

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Templating\EmailTemplatesDesc;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Orb\Util\Strings;
use Symfony\Component\HttpFoundation\Response;

/**
 * API access to feedback comments.
 *
 * @ApiModes("all")
 * @Rest\Route("/email_templates")
 * @ApiDoc(target="all", section="Email Templates")
 **/
class EmailsBlocksController extends BaseController
{
    /**
     * Retrieve the list of Email templates.
     *
     * @ApiDoc(
     *     section="Email Templates",
     *     resourceDescription="List of email templates",
     *     description="get templates",
     *     statusCodes={
     *         200="Returned if everything is OK",
     *     }
     * )
     *
     * @Rest\Get("/info", name="api_email_templates_info")
     *
     * @return View
     */
    public function getEmailTemplateInfoAction()
    {
        $tplDesc = new EmailTemplatesDesc();
        $list    = $tplDesc->getProcessedList($this->getContainer()->getTranslator());

        $list['custom']                     = [];
        $list['custom']['title']            = 'Custom Emails';
        $list['custom']['typeId']           = 'custom';
        $list['custom']['groups']           = [];
        $list['custom']['groups']['custom'] = [
            'groupId'   => 'custom',
            'title'     => 'Custom Emails',
            'templates' => [],
        ];

        $customEmails = $this->get('database_connection')
            ->fetchAll("SELECT id, name FROM templates WHERE name LIKE 'DeskPRO:emails_custom:%'");
        foreach ($customEmails as $tpl) {
            $name = Strings::extractRegexMatch('#^DeskPRO:emails_custom:(.*?).html.twig$#', $tpl['name'], 1).'.html';

            $list['custom']['groups']['custom']['templates'][] = [
                'typeId'    => 'custom',
                'groupId'   => 'custom',
                'is_custom' => true,
                'title'     => $name,
                'desc'      => '',
                'name'      => $tpl['name'],
                'showName'  => 'emails_custom/'.$name,
            ];
        }

        return View::create(
            $this->wrap([
                'list' => $list,
            ]),
            Response::HTTP_OK
        );
    }
}
