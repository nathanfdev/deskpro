<?php

/**
 * DeskPRO.
 */

namespace Application\LegacyApiBundle\Controller;

use Application\DeskPRO\Entity\Template;
use Application\DeskPRO\ResourceScanner\TemplateFiles;
use Application\DeskPRO\Templating\EmailTemplatesDesc;
use Application\DeskPRO\Templating\Templates\TemplateCustom;
use Application\DeskPRO\Templating\Templates\TemplateSet;
use Application\LegacyApiBundle\PermissionStrategy\AdminManagePermission;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use Orb\Util\Strings;

/**
 * @ApiModes("all")
 */
class TemplatesController extends AbstractController implements ProtectedControllerInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPermissionStrategy()
    {
        return new AdminManagePermission();
    }

    //###################################################################################################################
    // get-template-info
    //###################################################################################################################

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getTemplateInfoAction()
    {
        $tplfiles = new TemplateFiles();
        $map      = $tplfiles->getUserTemplates();

        $custom_templates = [];

        $list = $tplfiles->groupMap($map, $custom_templates);

        return $this->createApiResponse([
            'list'             => $list,
            'custom_templates' => $custom_templates,
        ]);
    }

    //###################################################################################################################
    // get-email-template-info
    //###################################################################################################################

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getEmailTemplateInfoAction()
    {
        $tpl_desc = new EmailTemplatesDesc();
        $list     = $tpl_desc->getProcessedList($this->container->getTranslator());

        $customTemplates = $this->container->getEm()->getRepository(Template::class)->findAll();
        $customTemplates = array_filter($customTemplates, function (Template $template) {
            return preg_match('#^DeskPRO:emails_#', $template->getName());
        });

        $customTemplateNames = array_map(function (Template $template) {
            return $template->getName();
        }, $customTemplates);

        if ($customTemplates) {
            foreach ($list as &$type_coll) {
                foreach ($type_coll['groups'] as &$group_coll) {
                    foreach ($group_coll['templates'] as &$tpl) {
                        if (in_array($tpl['name'], $customTemplateNames)) {
                            $tpl['is_custom'] = true;
                        } else {
                            $tpl['is_custom'] = false;
                        }
                    }
                }
            }
            unset($type_coll, $group_coll, $tpl);
        }

        $list['custom']                     = [];
        $list['custom']['title']            = 'Custom Emails';
        $list['custom']['typeId']           = 'custom';
        $list['custom']['groups']           = [];
        $list['custom']['groups']['custom'] = [
            'groupId'   => 'custom',
            'title'     => 'Custom Emails',
            'templates' => [],
        ];

        $custom_emails = $this->db->fetchAll("SELECT id, name FROM templates WHERE name LIKE 'DeskPRO:emails_custom:%'");
        foreach ($custom_emails as $tpl) {
            $name                                              = Strings::extractRegexMatch('#^DeskPRO:emails_custom:(.*?).html.twig$#', $tpl['name'], 1).'.html';
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

        return $this->createApiResponse([
            'list'             => $list,
            'custom_templates' => $customTemplates,
        ]);
    }

    //###################################################################################################################
    // get-template
    //###################################################################################################################

    /**
     * @param $name
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getTemplateAction($name)
    {
        if (strpos($name, 'EDIT_SIDEBAR_BLOCK:') === 0) {
            $block_id = substr($name, strlen('EDIT_SIDEBAR_BLOCK:'));
            $block    = $this->em->find('DeskPRO:PortalPageDisplay', $block_id);
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
            $this->container->getTranslator(),
            !$this->container->getLanguageData()->isMultiLang()
        );

        return $this->createApiResponse($data);
    }

    //###################################################################################################################
    // set-template
    //###################################################################################################################

    /**
     * @param $name
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function setTemplateAction($name)
    {
        $set      = $this->getTemplateSet();
        $template = null;

        try {
            $template = $set->getCustomTemplate($name);
        } catch (\InvalidArgumentException $e) {
            if (!$this->in->getBool('create_new')) {
                throw $this->createNotFoundException();
            }
        }

        if (!$template) {
            $template = $set->createCustomTemplate($name);
        }

        $template_code = $template->getTemplateCode();

        if ($template->getType() == 'email') {
            $subject = $this->in->getStringRaw('template.subject');
            $body    = $this->in->getStringRaw('template.body');

            $template_code->setSubject($subject);
            $template_code->setBody($body);
        } else {
            $code = $this->in->getStringRaw('template.code');
            $template_code->setCode($code);
        }

        try {
            $set->saveTemplate($template);
        } catch (\Twig_Error_Syntax $e) {
            return $this->createJsonResponse([
                'error'         => true,
                'error_syntax'  => true,
                'error_code'    => $e->getCode(),
                'error_message' => $e->getMessage(),
                'error_line'    => $e->getTemplateLine(),
            ], 400);
        } catch (\Twig_Error $e) {
            return $this->createJsonResponse([
                'error'         => true,
                'error_code'    => $e->getCode(),
                'error_message' => $e->getMessage(),
            ], 400);
        }

        return $this->createSuccessResponse([
            'name' => $template->getName(),
        ]);
    }

    //###################################################################################################################
    // delete-template
    //###################################################################################################################

    /**
     * @param $name
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteTemplateAction($name)
    {
        $set = $this->getTemplateSet();

        try {
            $template = $set->getTemplate($name);
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException();
        }

        if (!($template instanceof TemplateCustom)) {
            throw $this->createNotFoundException();
        }

        $set->deleteTemplate($template);

        return $this->createSuccessResponse([
            'old_name' => $name,
        ]);
    }

    //###################################################################################################################

    /**
     * @return TemplateSet
     */
    private function getTemplateSet()
    {
        $set = new TemplateSet(
            $this->em,
            $this->container->get('templating.email.twig')
        );

        return $set;
    }
}
