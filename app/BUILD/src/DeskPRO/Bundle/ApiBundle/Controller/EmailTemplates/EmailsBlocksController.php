<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\EmailTemplates;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Templating\EmailTemplatesDesc;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Orb\Util\Strings;
use Symfony\Component\HttpFoundation\Response;

/**
 * API access to feedback comments.
 *
 * @ApiModes("all")
 * @Feature("email_templates")
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
     *     },
     *     output="array"
     * )
     *
     * @Rest\Get("/email_templates/info", name="api_email_templates_info")
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
            'subGroups' => [
                'primary' => [
                    'subGroupId' => 'primary',
                    'templates'  => [],
                    'title'      => 'Custom templates',
                ],
            ],
        ];

        $customEmails = $this->get('database_connection')
            ->fetchAll("SELECT id, name FROM templates WHERE name LIKE 'SendmailBundle:emails_custom:%'");
        foreach ($customEmails as $tpl) {
            $name = Strings::extractRegexMatch('#^SendmailBundle:emails_custom:(.*?).html.twig$#', $tpl['name'], 1).'.html';

            $list['custom']['groups']['custom']['subGroups']['primary']['templates'][] = [
                'typeId'      => 'custom',
                'subGroupId'  => 'primary',
                'groupId'     => 'custom',
                'is_custom'   => true,
                'title'       => $name,
                'desc'        => '',
                'name'        => $tpl['name'],
                'newTemplate' => $tpl['name'],
                'showName'    => 'emails_custom/'.$name,
                'viewModel'   => 'CustomTemplate',
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
