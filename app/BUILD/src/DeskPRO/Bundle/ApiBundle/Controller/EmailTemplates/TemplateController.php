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
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Entity\PortalPageDisplay;
use Application\DeskPRO\Templating\Templates\TemplateCustom;
use Application\EmailBundle\SwiftMailer\Message\Message;
use Application\EmailBundle\Templating\Templates\EmailTemplateCode;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiUnstable;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\EmailTemplateType;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\SendmailBundle\Factory\AgentViewModelFactory;
use DeskPRO\Bundle\SendmailBundle\Factory\UserViewModelFactory;
use DeskPRO\Bundle\SendmailBundle\Render\EmailRenderer;
use DeskPRO\Bundle\SendmailBundle\Templating\Templates\TemplateSet;
use DeskPRO\Bundle\SendmailBundle\Twig\PreProcessor\EmailPreProcessor;
use DeskPRO\Bundle\SendmailBundle\Twig\TwigEngine;
use DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Twig_Loader_Chain;

/**
 * API access to person settings.
 *
 * @ApiModes("all")
 * @Feature("email_templates")
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
     *     output="array"
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
     *     input={
     *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\EmailTemplateType",
     *     },
     *     output="array"
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
            $template = $set->createCustomTemplate($name, 'email');
        }

        $templateCode = $template->getTemplateCode();

        $data = $form->getData();
        if ($template->getType() == 'email') {
            $templateCode->setSubject($data['subject']);
            $templateCode->setBody($data['body']);
        } elseif (preg_match('#^(DeskPRO|SendmailBundle):blocks#', $template->getName())) {
            $templateCode->setCode($data['body']);
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
     *     input="array",
     *     output="string"
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
        $tplName = uniqid('SendmailBundle:emails_', true);

        $viewModel      = $request->request->get('template');
        $group          = $request->request->get('group');
        $lang           = $request->request->get('lang');
        $extraTemplates = $request->request->get('extraTemplates');

        try {
            $view = $this->renderPreview($request, $code, $tplName, $viewModel, $group, $lang, $extraTemplates);
        } catch (\Twig_Error $e) {
            return new View(['error' => $e->getRawMessage(), 'line' => $e->getTemplateLine()]);
        }

        return new View($view);
    }

    /**
     * @ApiDoc(
     *     section="Email Templates",
     *     description="Render a template to preview",
     *     input="array",
     *     output="string"
     *)
     * @ApiUnstable()
     * @Rest\Post("/send_preview")
     *
     * @param Request $request
     *
     * @return View
     */
    public function postSendPreviewEmailAction(Request $request)
    {
        $body    = $request->request->get('body');
        $subject = $request->request->get('subject');
        $tplName = uniqid('SendmailBundle:emails_', true);

        $viewModel      = $request->request->get('viewModel');
        $group          = $request->request->get('group');
        $lang           = $request->request->get('lang');
        $fromAccount    = $request->request->get('from');
        $to             = $request->request->get('to');
        $extraTemplates = $request->request->get('extraTemplates');

        $code = '<dp:subject>'.$subject.'</dp:subject>'."\n\n".$body;

        /** @var Message $message */
        $message   = $this->get('mailer')->createMessage();
        $emailCode = $this->renderPreview($request, $code, $tplName, $viewModel, $group, $lang, $extraTemplates);
        $message->setBody($emailCode->getBody(), 'text/html');
        $message->setSubject($emailCode->getSubject());
        foreach ($emailCode->getAttachments() as $blob) {
            $message->attachBlob($blob);
        }
        $message->setTo($to);
        if (!empty($args['attachments'])) {
            foreach ($args['attachments'] as $attach) {
                $message->attach($attach);
            }
        }
        $this->get('mailer')->send($message);

        return new View('OK');
    }

    /**
     * @param Request $request
     * @param string  $code
     * @param string  $tplName
     * @param string  $viewModel
     * @param string  $group
     * @param string  $lang
     * @param array   $templates
     *
     * @return EmailTemplateCode
     */
    private function renderPreview($request, $code, $tplName, $viewModel, $group, $lang, $templates)
    {
        /** @var Language $language */
        $language = $this->getManager()->getRepository(Language::class)->findOneBy(['locale' => $lang]);
        /** @var AgentViewModelFactory|UserViewModelFactory $factory */
        $factory = $this->get('email.'.$group.'_viewmodel_factory');
        $action  = 'create'.$viewModel.'Model';

        if (!is_callable([$factory, $action])) {
            throw $this->createNotFoundException('Missing method '.$action.' in factory');
        }

        $dataFactory = $this->get('email.preview_fake_data_factory');
        $arguments   = $dataFactory->getArguments($factory, $action, $request);

        $recipient = new Person();
        $recipient->setFirstName('FirstName');
        $recipient->setLastName('LastName');
        $email = new PersonEmail();
        $email->setEmail('test@example.com');
        $recipient->setPrimaryEmail($email);
        $recipient->setPassword('Password1234');
        /** @var EmailBaseType $model */
        $model = call_user_func_array([$factory, $action], $arguments);

        $serializationContext = new SideloadSerializationContext();
        $serializationContext->setInlineSideloads(true);
        $recipient = $this->get('api_serializer.handler.person')->createModel($recipient, $serializationContext);
        $model->setRecipient($recipient);
        $model->setSiteUrl($this->container->getBrandSetting('core.site_url'));
        $model->setSiteName($this->container->getBrandSetting('core.site_name'));
        $model->setDeskproUrl($this->container->getBrandSetting('core.deskpro_url'));

        $preProcessor = new EmailPreProcessor();
        $code         = $preProcessor->process($code, $tplName);

        $twig = clone $this->get('templating.new_email.twig');
        $twig->setCache(false);
        $templates[$tplName] = $code;
        $stringLoader        = new \Twig_Loader_Array($templates);
        $hybridLoader        = $this->get('templating.new_email.twig.loader');
        $loader              = new Twig_Loader_Chain([$stringLoader, $hybridLoader]);
        $twig->setLoader($loader);

        /** @var TwigEngine $twigEngine */
        $twigEngine = $this->get('templating.new_email.twig.engine');
        $twigEngine->setEnvironment($twig);

        /** @var EmailRenderer $renderer */
        $renderer = $this->get('email.email_renderer');
        $renderer->setTemplateEngine($twigEngine);

        $view = null;
        $this->get('translator')->setTemporaryLanguage(
            $language,
            function () use ($tplName, $model, $renderer, &$view) {
                $view = $renderer->render($tplName, $model);
            }
        );

        return $view;
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
