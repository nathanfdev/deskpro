<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\EmailTemplates;

use Application\DeskPRO\Dpql\Exception;
use Application\DeskPRO\Entity\DataStore;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\PortalPageDisplay;
use Application\DeskPRO\Entity\Template;
use Application\DeskPRO\Entity\TicketTrigger;
use Application\DeskPRO\Templating\Templates\TemplateCustom;
use Application\DeskPRO\Tickets\Actions\AbstractEmailAction;
use Application\DeskPRO\Tickets\TicketActions\ActionInterface;
use Application\EmailBundle\SwiftMailer\Message\Message;
use Application\EmailBundle\Templating\Templates\EmailTemplateCode;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiUnstable;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\Feature;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\EmailTemplateType;
use DeskPRO\Bundle\AppBundle\Templating\EmailTemplatesDesc;
use DeskPRO\Bundle\SendmailBundle\Factory\AgentViewModelFactory;
use DeskPRO\Bundle\SendmailBundle\Factory\UserViewModelFactory;
use DeskPRO\Bundle\SendmailBundle\Templating\Templates\TemplateSet;
use DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

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
            if ($template->templateFileExists()) {
                $template = $set->getTemplate($name);
            } else {
                return new View();
            }
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
     * @throws \Exception
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
     *     description="Send preview of a template",
     *     input="array",
     *     output="string"
     *)
     * @ApiUnstable()
     * @Rest\Post("/send_preview")
     *
     * @param Request $request
     *
     * @throws \Exception
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
     * @ApiDoc(
     *     section="Email Templates",
     *     description="Get legacy templates to upgrade",
     *     output="string"
     *)
     * @Rest\Get("/legacy_templates")
     *
     * @return View
     */
    public function getLegacyTemplatesAction()
    {
        $templates = $this->getManager()->getRepository(Template::class)->getLegacyTemplates();

        return new View($templates);
    }

    /**
     * @ApiDoc(
     *     section="Email Templates",
     *     description="Revert legacy template to hardcoded template",
     *     output="string"
     *)
     * @Rest\Get("/revert_legacy_template/{id}")
     *
     * @param $id
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function getRevertLegacyTemplateAction($id)
    {
        $em        = $this->getManager();
        $templates = $em->getRepository(Template::class)->getLegacyTemplates();

        foreach ($templates as $template) {
            if ($template[0]['id'] == $id) {
                // if the template is used in some triggers
                if ($template[1]) {
                    $triggers = explode(',', $template[1]);
                    foreach ($triggers as $triggerData) {
                        $info      = explode('-', $triggerData);
                        $triggerId = $info[1];
                        /** @var TicketTrigger $trigger */
                        $trigger = $em->getRepository(TicketTrigger::class)->find($triggerId);
                        $this->upgradeTrigger($trigger, $template[0]->getName());
                        $em->persist($trigger);
                    }
                }
                $dataStore = new DataStore();
                $dataStore->setName('legacy_email_template.'.md5($template[0]->getName()));
                $dataStore->setData('name', $template[0]->getName());
                $dataStore->setData('code', $template[0]->getTemplateCode());

                $em->persist($dataStore);
                $em->remove($template[0]);
            }
        }
        $em->flush();
    }

    /**
     * @ApiDoc(
     *     section="Email Templates",
     *     description="Delete legacy template",
     *     output="string"
     *)
     * @Rest\Delete("/legacy_template/{id}")
     *
     * @param $id
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteLegacyTemplateAction($id)
    {
        $em        = $this->getManager();
        $templates = $em->getRepository(Template::class)->getLegacyTemplates();

        foreach ($templates as $template) {
            if ($template[0]['id'] == $id) {
                // if the template is used in some triggers
                if ($template[1]) {
                    $triggers = explode(',', $template[1]);
                    foreach ($triggers as $triggerData) {
                        $info      = explode('-', $triggerData);
                        $triggerId = $info[1];
                        /** @var TicketTrigger $trigger */
                        $trigger = $em->getRepository(TicketTrigger::class)->find($triggerId);
                        $this->upgradeTrigger($trigger, $template[0]->getName(), false);
                        $em->persist($trigger);
                    }
                }
                $dataStore = new DataStore();
                $dataStore->setName('legacy_email_template.'.md5($template[0]->getName()));
                $dataStore->setData('name', $template[0]->getName());
                $dataStore->setData('code', $template[0]->getTemplateCode());

                $em->persist($dataStore);
                $em->remove($template[0]);
            }
        }
        $em->flush();
    }

    private function upgradeTrigger(TicketTrigger $trigger, $templateName, $replace = true)
    {
        $templatesDesc = new EmailTemplatesDesc();
        $manifest      = $templatesDesc->getManifest();

        /** @var ActionInterface[] $actions */
        $actionsClone = clone $trigger->actions;
        $actions      = $actionsClone->getActions();
        $newActions   = [];

        foreach ($actions as $key => $action) {
            $options = $action->getActionOptions();
            if ($options['template'] !== $templateName) {
                continue;
            }
            if ($action instanceof AbstractEmailAction) {
                if ($replace) {
                    $manifestKey = array_search($options['template'], array_column($manifest, 'name'));
                    $info        = $manifest[$manifestKey];

                    if ($info === false || !isset($info['newTemplate'])) {
                        throw new \Exception('template not present in manifest');
                    }
                    $options['template'] = $info['newTemplate'];

                    $class        = get_class($action);
                    $class        = str_replace('Email', 'NewEmail', $class);
                    $newActions[] = new $class($options->all());
                }
                unset($actions[$key]);
            }
        }
        foreach ($newActions as $newAction) {
            $actions[] = $newAction;
        }

        $actionsClone->setActions($actions);
        $trigger->setActions($actionsClone);
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
     * @throws \Exception
     *
     * @return EmailTemplateCode
     */
    private function renderPreview($request, $code, $tplName, $viewModel, $group, $lang, $templates)
    {
        $renderer = $this->get('email.email_renderer');

        $dataFactory = $this->get('email.preview_fake_data_factory');

        /** @var AgentViewModelFactory|UserViewModelFactory $factory */
        $factory = $this->get('email.'.$group.'_viewmodel_factory');
        $action  = 'create'.$viewModel.'Model';

        if (!is_callable([$factory, $action])) {
            throw $this->createNotFoundException('Missing method '.$action.' in factory');
        }

        $arguments = $dataFactory->getArguments($factory, $action, $request);

        /** @var EmailBaseType $model */
        $model = call_user_func_array([$factory, $action], $arguments);

        /** @var Language $language */
        $language = $this->getRepository(Language::class)->findOneBy(['locale' => $lang]);

        return $renderer->renderPreview($code, $tplName, $model, $language, $templates);
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
