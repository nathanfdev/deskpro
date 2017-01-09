<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

use Application\DeskPRO\Dpql\Exception;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\PortalPageDisplay;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Templating\Templates\TemplateCustom;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiUnstable;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\EmailTemplateType;
use DeskPRO\Bundle\SendmailBundle\Render\EmailRenderer;
use DeskPRO\Bundle\SendmailBundle\Templating\Templates\TemplateSet;
use DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * API access to person settings.
 *
 * @ApiModes("all")
 * @Feature("new_email_templates")
 * @Rest\Route("/email_templates")
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
     * @Rest\Get("/template/{name}")
     *
     * @param $name
     *
     * @return View
     */
    public function getTemplateAction($name)
    {
        if (strpos($name, 'EDIT_SIDEBAR_BLOCK:') === 0) {
            $blockId = substr($name, strlen('EDIT_SIDEBAR_BLOCK:'));
            $block   = $this->getManager()->getRepository(PortalPageDisplay::class)->find($blockId);
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
     * @ApiDoc(
     *     section="Email Templates",
     *     description="Save a template",
     *     requirements={
     *         {
     *             "name"="name",
     *             "description"="The template name",
     *             "dataType"="string"
     *         }
     *     },
     *)
     * @ApiUnstable()
     * @Rest\Post("/template/{name}")
     *
     * @param Request $request
     * @param         $name
     *
     * @throws Exception
     *
     * @return View
     */
    public function setTemplateAction(Request $request, $name)
    {
        $set      = $this->getTemplateSet();
        $template = null;

        $form = $this->createForm(EmailTemplateType::class);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        try {
            $template = $set->getCustomTemplate($name);
        } catch (\InvalidArgumentException $e) {
            if (!$request->request->get('create_new')) {
                throw $this->createNotFoundException();
            }
        }

        if (!$template) {
            $template = $set->createCustomTemplate($name);
        }

        $templateCode = $template->getTemplateCode();

        $data = $form->getData();
        if ($template->getType() == 'email') {
            $templateCode->setSubject($data['subject']);
            $templateCode->setBody($data['body']);
        } else {
            throw new Exception('Only "email" templates can be updated '.$template->getType());
        }

        try {
            $set->saveTemplate($template);
        } catch (\Twig_Error_Syntax $e) {
            return new View([
                'error'         => true,
                'error_syntax'  => true,
                'error_code'    => $e->getCode(),
                'error_message' => $e->getMessage(),
                'error_line'    => $e->getTemplateLine(),
            ], 400);
        } catch (\Twig_Error $e) {
            return new View([
                'error'         => true,
                'error_code'    => $e->getCode(),
                'error_message' => $e->getMessage(),
            ], 400);
        }

        return new View(['success' => true, 'name' => $template->getName()]);
    }

    /**
     * @ApiDoc(
     *     section="Email Templates",
     *     description="Delete a custom template and retrieve the original one",
     *     requirements={
     *         {
     *             "name"="name",
     *             "description"="The template name",
     *             "dataType"="string"
     *         }
     *     },
     *)
     * @ApiUnstable()
     * @Rest\Delete("/template/{name}")
     *
     * @param $name
     *
     * @return View
     */
    public function resetTemplateAction($name)
    {
        if (strpos($name, 'EDIT_SIDEBAR_BLOCK:') === 0) {
            $blockId = substr($name, strlen('EDIT_SIDEBAR_BLOCK:'));
            $block   = $this->getManager()->getRepository(PortalPageDisplay::class)->find($blockId);
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

        if ($template instanceof TemplateCustom) {
            $set->deleteTemplate($template);
            $template = $set->getTemplate($name);
        }

        $data = $set->exportTemplateToArray(
            $template,
            $this->get('translator'),
            !$this->get('settings_resolver')->getGlobalSettings()->get('core.enable_languages')
        );

        return new View($data);
    }

    /**
     * @ApiDoc(
     *     section="Email Templates",
     *     description="Render a template to preview",
     *)
     * @ApiUnstable()
     * @Rest\Post("/render_template")
     *
     * @param Request $request
     *
     * @return View
     */
    public function postRenderTemplateAction(Request $request)
    {
        $code    = $request->request->get('code');
        $tplName = uniqid('string_template_', true);
        $twig    = clone $this->get('templating.new_email.twig');
        $twig->setCache(false);
        $twig->setLoader(new \Twig_Loader_Array([$tplName => $code]));

        /** @var EmailRenderer $renderer */
        $renderer = $this->get('email.email_renderer');
        $renderer->setTemplateEngine($twig);

        $viewModel = $request->request->get('template');
        $group     = $request->request->get('group');
        $factory   = $this->get('email.'.$group.'_viewmodel_factory');
//        $ticket    = $this->getRepository(Ticket::class)->findOneBy([]);
        $ticket = $this->getRepository(Download::class)->findBy([]);
        $action = 'create'.$viewModel.'Model';
        if (!is_callable([$factory, $action])) {
            throw $this->createNotFoundException('Missing method '.$action.' in factory');
        }
        /** @var EmailBaseType $model */
        $model = $factory->$action(
            $ticket,
            $ticket
        );

        $recipient = new Person();
        $recipient->setFirstName('FirstName');
        $recipient->setLastName('LastName');
        $email = new PersonEmail();
        $email->setEmail('test@example.com');
        $recipient->setPrimaryEmail($email);
        $recipient->setPassword('Password1234');
        $model->setRecipient($recipient);

        return new View($renderer->render($tplName, $model));
    }

    /**
     * @return TemplateSet
     */
    private function getTemplateSet()
    {
        $set = new TemplateSet(
            $this->getManager(),
            $this->container->get('templating.new_email.twig')
        );

        return $set;
    }
}
