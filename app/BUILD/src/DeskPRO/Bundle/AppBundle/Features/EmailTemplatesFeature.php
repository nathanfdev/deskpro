<?php

namespace DeskPRO\Bundle\AppBundle\Features;

use Application\DeskPRO\Entity\DataStore;
use Application\DeskPRO\Entity\Template;
use Application\DeskPRO\EntityRepository\Template as TemplateRepository;
use Application\DeskPRO\Templating\Templates\EmailTemplateCode;
use Application\DeskPRO\Templating\Templates\TemplateCode;
use Application\DeskPRO\Templating\Templates\TemplateCustom;
use DeskPRO\Bundle\AppBundle\Templating\EmailTemplatesDesc;
use DeskPRO\Bundle\SendmailBundle\Factory\AgentViewModelFactory;
use DeskPRO\Bundle\SendmailBundle\Factory\UserViewModelFactory;
use DeskPRO\Bundle\SendmailBundle\Templating\Templates\TemplateSet;
use DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EmailTemplatesFeature.
 */
class EmailTemplatesFeature extends AbstractBetaFeature
{
    protected static $legacyBlocks = [
        'DeskPRO:emails_common:email-header.html.twig'    => 'SendmailBundle:blocks:header.html.twig',
        'DeskPRO:emails_common:email-footer.html.twig'    => 'SendmailBundle:blocks:footer.html.twig',
        'DeskPRO:emails_common:email-custom-css.css.twig' => 'SendmailBundle:blocks:resources.html.twig',
    ];

    protected $migratedCustomTemplates = [];

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'email_templates';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'New Email Templates';
    }

    /**
     * {@inheritdoc}
     */
    public function getShortDescription()
    {
        return 'Improved email templating and new template editor.';
    }

    /**
     * {@inheritdoc}
     */
    public function getEnableDescription()
    {
        return <<<'HTML'
Enable new Email templates<br/><br/>
Installing Email templates will copy your email templates over to the new system and backup previous templates,
some emails might not be able to be migrated automatically.<br />
There will be a link in the new Email Template Editor
to allow to carry them over.

You will be able to disable this Beta and restoring your previous state. However any modifications applied during the beta 
will be discarded.

HTML;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisableDescription()
    {
        return <<<'HTML'
Disable new Email templates.<br/><br/>
Disabling the Beta will restore Emails templates to the previous state.<br />
<br />
Warning! Any modification applied during the Beta will be DISCARDED

HTML;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailability()
    {
        return [self::AVAILABLE_AT_QA];
    }

    /**
     * {@inheritdoc}
     */
    public function needAgentReload()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeEnable(ContainerInterface $container)
    {
        /** @var EntityManager $em */
        $em = $container->get('doctrine.orm.default_entity_manager');
        $this->copyLegacyBlocks($em, $container);
        $this->copyLegacyTemplates($em, $container);
        $this->replaceTriggers($em, $container);
    }

    /**
     * {@inheritdoc}
     */
    public function beforeDisable(ContainerInterface $container)
    {
        /** @var EntityManager $em */
        $em = $container->get('doctrine.orm.default_entity_manager');
        $this->restoreLegacyTemplates($em, $container);
        $this->restoreTriggers($em, $container);
    }

    private function copyLegacyBlocks(EntityManager $em, ContainerInterface $container)
    {
        $set = $this->getTemplateSet($em, $container);

        /** @var TemplateRepository $templateRepo */
        $templateRepo = $em->getRepository(Template::class);
        $blocks       = $templateRepo->findBy(['name' => array_keys(self::$legacyBlocks)]);

        foreach ($blocks as $previousBlock) {
            /** @var Template $previousBlock */
            $template = $set->createCustomTemplate(self::$legacyBlocks[$previousBlock->getName()]);

            /** @var TemplateCode $templateCode */
            $templateCode = $template->getTemplateCode();

            $code = $previousBlock->getTemplateCode();
            if ($previousBlock->getName() === 'DeskPRO:emails_common:email-custom-css.css.twig') {
                $code = $this->convertResourcesCode($code);
            }
            $templateCode->setCode($code);
            $set->saveTemplate($template);
            $this->saveLegacyTemplate($em, $previousBlock);
            $em->remove($previousBlock);
        }

        $em->flush();
    }

    private function copyLegacyTemplates(EntityManager $em, ContainerInterface $container)
    {
        $set = $this->getTemplateSet($em, $container);

        $qb = $em->createQueryBuilder();
        $qb
            ->select('t')
            ->from(Template::class, 't')
            ->where('t.name LIKE :name')
            ->setParameter('name', 'DeskPRO:emails_%')
        ;

        $templates     = $qb->getQuery()->getResult();
        $templatesDesc = new EmailTemplatesDesc();
        $manifest      = $templatesDesc->getManifest();

        $renderer = $container->get('email.email_renderer');

        $dataFactory = $container->get('email.preview_fake_data_factory');

        $language = $container->get('language_manager')->getLanguageStack()->getDefaultLanguage();

        foreach ($templates as $previousTemplate) {
            /** @var Template $previousTemplate */
            if (strpos($previousTemplate->getName(), 'DeskPRO:emails_custom') === 0) {
                $newTemplateName = str_replace('DeskPRO:emails_custom', 'SendmailBundle:emails_custom', $previousTemplate->getName());
            } else {
                $key = array_search($previousTemplate->getName(), array_column($manifest, 'name'));
                if ($key === false) {
                    continue;
                }
                $info = $manifest[$key];

                if ($info === false || !isset($info['newTemplate'])) {
                    continue;
                }
                $newTemplateName = $info['newTemplate'];
            }

            /** @var Template $template */
            $templateEntity = $em->getRepository(Template::class)->findOneBy(['name' => $newTemplateName]);
            if (!$templateEntity) {
                $template = $set->createCustomTemplate($newTemplateName);
            } else {
                $template = TemplateCustom::createFromEntity($templateEntity);
            }

            /** @var EmailTemplateCode $templateCode */
            $templateCode = $template->getTemplateCode();

            if (get_class($templateCode) !== EmailTemplateCode::class) {
                continue;
            }

            $code = $previousTemplate->getTemplateCode();
            $templateCode->setCode($code);

            $body = $this->convertTemplateCode($templateCode->getBody());
            if (!$body) {
                continue;
            }
            $templateCode->setBody($body);

            $subject = $this->convertTemplateCode($templateCode->getSubject(), true);
            if (!$subject) {
                continue;
            }
            $templateCode->setSubject($subject);

            // Loader issue cause this check to fail for all templates, I keep it commented until I sort it out.
//            try {
//                if (strpos($newTemplateName, 'SendmailBundle:emails_custom:') === 0) {
//                    $factory   = $container->get('email.custom_viewmodel_factory');
//                    $arguments = $dataFactory->getArguments($factory, 'createCustomTemplateModel', null);
//                    $model = call_user_func_array([$factory, 'createCustomTemplateModel'], $arguments);
//                } else {
//                    /** @var EmailBaseType $model */
//                    $templatesDesc = new EmailTemplatesDesc();
//                    $manifest      = $templatesDesc->getManifest();
//                    $viewModel     = false;
//                    foreach ($manifest as $t) {
//                        if (isset($t['newTemplate']) && $t['newTemplate'] === $newTemplateName) {
//                            if ($t['viewModel']) {
//                                $viewModel = $t['viewModel'];
//                            }
//                            break;
//                        }
//                    }

//                    /** @var AgentViewModelFactory|UserViewModelFactory $factory */
//                    $factory = strpos($newTemplateName, 'SendmailBundle:emails_agent:') === 0
//                        ? $container->get('email.agent_viewmodel_factory')
//                        : $container->get('email.user_viewmodel_factory');
//                    $action  = 'create'.$viewModel.'Model';

//                    $arguments = $dataFactory->getArguments($factory, $action, null);

//                    if (!is_callable([$factory, $action])) {
//                        return false;
//                    }

//                    $model = call_user_func_array([$factory, $action], $arguments);
//                }

//                $tplName = uniqid('SendmailBundle:emails_', true);

//                $renderer->renderPreview($templateCode->getCode(), $tplName, $model, $language, $templates);
//            } catch (\Throwable $e) {
//                continue;
//            }

            $this->migratedCustomTemplates[$previousTemplate->getName()] = $newTemplateName;
            $set->saveTemplate($template);
            $this->saveLegacyTemplate($em, $previousTemplate);
            $em->remove($previousTemplate);
        }
        $em->flush();
    }

    private function convertResourcesCode($code)
    {
        $code = preg_replace('/.+/', '    $0', $code);

        return <<<CODE
{{ default_css | raw }}
<style>
    /* Enter your own custom CSS here */
$code
</style>
CODE;
    }

    /**
     * @param string $code
     * @param bool   $subject
     *
     * @return bool|mixed|null|string|string[]
     */
    private function convertTemplateCode($code, $subject = false)
    {
        $code = $this->uniformiseVariableSyntax($code);
        $code = str_replace(
            array_keys($this->getTemplateUpgradePatterns()),
            array_values($this->getTemplateUpgradePatterns()),
            $code
        );
        if (preg_match('/<dp:/', $code)) {
            return false;
        }
        $code      = preg_replace('/{{\s*(phrase\s*\([^{]+\))\|\s*raw\s*}}/', '{{ $1 }}', $code);
        $whiteList = $this->getVariableWhiteList();
        if (preg_match_all('/{{[^}]+}}/', $code, $matches)) {
            foreach ($matches[0] as $match) {
                if (in_array($match, $whiteList)) {
                    continue;
                }
                // Only allow phrases without variables, excluding variables or phrases with variables
                if (preg_match('/^{{\s*phrase\s*\([^{]+\)\s*}}$/', $match)) {
                    continue;
                } else {
                    return false;
                }
            }
        }

        if ($subject) {
            return $code;
        }

        $code = preg_replace('/.+/', '    $0', $code);

        return <<<CODE
<html>
<head>
    {% include 'SendmailBundle:blocks:resources.html.twig' %}
</head>
<body>
{% include 'SendmailBundle:emails_common:email_code_top.html.twig' %}

{% include 'SendmailBundle:blocks:header.html.twig' %}

<container>

$code

</container>

{% include 'SendmailBundle:blocks:footer.html.twig' %}

{% include 'SendmailBundle:emails_common:email_code_bottom.html.twig' %}
</body>
</html>
CODE;
    }

    private function uniformiseVariableSyntax($code)
    {
        $code = preg_replace_callback(
            '|{{\s*([._a-z]+)\s*}}|',
            function ($matches) {
                return '{{ '.$matches[1].' }}';
            },
            $code
        );

        return $code;
    }

    private function getTemplateUpgradePatterns()
    {
        return [
          '<dp:ticket-messages />' => <<<'CODE'
{% for message in ticket_messages %}
    {% if not context.message_limit or loop.index0 < context.message_limit %}
        {% include 'SendmailBundle:emails_common:ticket_message_row.html.twig' with { message: message, ticketdisplay: context.ticketdisplay } %}
    {% endif %}
{% endfor %}
CODE
            ,
          '{{ ticket.person.primary_email.email }}'                             => '{{ ticket.person.primary_email }}',
          '{{ ticket.agent.primary_email.email }}'                              => '{{ ticket.agent.primary_email }}',
          '{{ article.person.display_name_user }}'                              => '{{ article.person.display_name }}',
          '{{ news.person.display_name_user }}'                                 => '{{ news.person.display_name }}',
          '{{ download.person.display_name_user }}'                             => '{{ download.person.display_name }}',
          '{{ download.content_desc }}'                                         => '{{ download.content }}',
          '{{ download.filename }}'                                             => '{{ download.blob.filename }}',
          '{{ download.readable_filesize }}'                                    => '{{ download.blob.filesize_readable }}',
          '{{ feedback.person.display_name_user }}'                             => '{{ feedback.person.display_name }}',
          '{{ portal_url(ticket) }}'                                            => '{{ ticket_link }}',
          '{{ portal_url(article) }}'                                           => '{{ article_link }}',
          '{{ url_full(\'portal_reset_password_process\', {\'code\': code}) }}' => '{{ reset_url }}',
        ];
    }

    private function getVariableWhiteList()
    {
        return array_merge(array_values($this->getTemplateUpgradePatterns()), [
            '{{ ticket.subject }}',
            '{{ ticket.department.title }}',
            '{{ ticket.product.title }}',
            '{{ ticket.product.title }}',
            '{{ ticket.category.title }}',
            '{{ ticket.workflow.title }}',
            '{{ ticket.priority.title }}',
            '{{ ticket.id }}',
            '{{ ticket.ref }}',
            '{{ ticket.date_created|date(\'full\') }}',
            '{{ ticket.agent.display_name }}',
            '{{ ticket.person.display_name }}',
            '{{ article.title }}',
            '{{ article.content }}',
            '{{ news.title }}',
            '{{ news.content }}',
            '{{ news.link }}',
            '{{ news.category.title }}',
            '{{ download.title }}',
            '{{ download.slug }}',
            '{{ download.date_created|date(\'full\') }}',
            '{{ feedback.status }}',
            '{{ message }}',
        ]);
    }

    private function replaceTriggers(EntityManager $em, ContainerInterface $container)
    {
        $emailActions = [
            'SendAgentEmail'         => 'SendAgentNewEmail',
            'SendUserEmail'          => 'SendUserNewEmail',
            'SendSpecificUserEmail'  => 'SendSpecificUserNewEmail',
            'SendArbitraryUserEmail' => 'SendArbitraryUserNewEmail',
        ];

        $dbConnection  = $container->get('doctrine')->getConnection('default');
        $templatesDesc = new EmailTemplatesDesc();
        $manifest      = $templatesDesc->getManifest();

        $templatesStmt = $dbConnection->executeQuery('SELECT `name` FROM `templates` WHERE name LIKE ?', ['DeskPRO:emails_%']);
        $templates     = [];
        foreach ($templatesStmt as $template) {
            $templates[] = $template['name'];
        }

        $tables = [
            'ticket_triggers',
            'ticket_escalations',
        ];
        foreach ($tables as $table) {
            $triggers = $dbConnection->executeQuery('SELECT `id`,`actions` FROM `'.$table.'`', []);
            foreach ($triggers as $trigger) {
                $id      = $trigger['id'];
                $changed = false;
                try {
                    $actions = json_decode($trigger['actions'], true);
                } catch (\Exception $e) {
                    continue;
                }
                foreach ($actions['@DATA']['actions'] as $actionId => $action) {
                    if ($action['type'] && isset($emailActions[$action['type']])) {
                        if (!in_array($action['options']['template'], $templates)) {
                            $manifestKey = array_search($action['options']['template'], array_column($manifest, 'name'), true);
                            $newTemplate = '';
                            if ($manifestKey) {
                                $newTemplate = $manifest[$manifestKey]['newTemplate'];
                            } elseif (isset($this->migratedCustomTemplates[$action['options']['template']])) {
                                $newTemplate = $this->migratedCustomTemplates[$action['options']['template']];
                            }

                            $actions['@DATA']['actions'][$actionId]['type']                = $emailActions[$action['type']];
                            $actions['@DATA']['actions'][$actionId]['options']['template'] = $newTemplate;
                            $changed                                                       = true;
                        }
                    }
                }
                if ($changed) {
                    $dbConnection->executeQuery(
                        'UPDATE `'.$table.'` SET `actions` = ? WHERE `id` = ?',
                        [json_encode($actions), $id]
                    );
                }
            }
        }
    }

    /**
     * @param EntityManager      $em
     * @param ContainerInterface $container
     *
     * @return TemplateSet
     */
    private function getTemplateSet(EntityManager $em, ContainerInterface $container)
    {
        $set = new TemplateSet(
            $em,
            $container->get('templating.new_email.twig')
        );

        return $set;
    }

    /**
     * @param EntityManager $em
     * @param Template      $template
     */
    private function saveLegacyTemplate(EntityManager $em, $template)
    {
        $dataStore = new DataStore();
        $dataStore->setName('legacy_email_template.'.md5($template->getName()));
        $dataStore->setData('name', $template->getName());
        $dataStore->setData('code', $template->getTemplateCode());

        $em->persist($dataStore);
    }

    private function restoreTriggers(EntityManager $em, ContainerInterface $container)
    {
        $emailActions = [
            'SendAgentNewEmail'         => 'SendAgentEmail',
            'SendUserNewEmail'          => 'SendUserEmail',
            'SendSpecificUserNewEmail'  => 'SendSpecificUserEmail',
            'SendArbitraryUserNewEmail' => 'SendArbitraryUserEmail',
        ];

        $dbConnection  = $container->get('doctrine')->getConnection('default');
        $templatesDesc = new EmailTemplatesDesc();
        $manifest      = $templatesDesc->getManifest();

        $manifest = array_values(array_filter($manifest, function ($entry) {
            return !empty($entry['newTemplate']);
        }));

        $templatesStmt = $dbConnection->executeQuery('SELECT `name` FROM `templates` WHERE name LIKE ?', ['DeskPRO:emails_%']);
        $templates     = [];
        foreach ($templatesStmt as $template) {
            $templates[] = $template['name'];
        }

        $tables = [
            'ticket_triggers',
            'ticket_escalations',
        ];
        foreach ($tables as $table) {
            $triggers = $dbConnection->executeQuery('SELECT `id`,`actions` FROM `'.$table.'`', []);
            foreach ($triggers as $trigger) {
                $id      = $trigger['id'];
                $changed = false;
                try {
                    $actions = json_decode($trigger['actions'], true);
                } catch (\Exception $e) {
                    continue;
                }
                foreach ($actions['@DATA']['actions'] as $actionId => $action) {
                    if ($action['type'] && isset($emailActions[$action['type']])) {
                        if (!in_array($action['options']['template'], $templates)) {
                            $manifestKey = array_search(
                                $action['options']['template'],
                                array_column($manifest, 'newTemplate'),
                                true
                            );
                            $info = $manifest[$manifestKey];

                            $actions['@DATA']['actions'][$actionId]['type']                = $emailActions[$action['type']];
                            $actions['@DATA']['actions'][$actionId]['options']['template'] = $info['name'];
                            $changed                                                       = true;
                        }
                    }
                }
                if ($changed) {
                    $dbConnection->executeQuery(
                        'UPDATE `'.$table.'` SET `actions` = ? WHERE `id` = ?',
                        [json_encode($actions), $id]
                    );
                }
            }
        }
    }

    private function restoreLegacyTemplates(EntityManager $em, ContainerInterface $container)
    {
        /** @var \Application\DeskPRO\EntityRepository\DataStore $dataStoreRepository */
        $dataStoreRepository = $em->getRepository(DataStore::class);
        $legacyTemplates     = $dataStoreRepository->getByPrefix('legacy_email_template');

        $set = $this->getTemplateSet($em, $container);

        /** @var DataStore $legacyTemplate */
        foreach ($legacyTemplates as $legacyTemplate) {
            /** @var Template $template */
            $template = $set->createCustomTemplate($legacyTemplate->getData('name'));

            /** @var EmailTemplateCode $templateCode */
            $templateCode = $template->getTemplateCode();

            if (get_class($templateCode) !== EmailTemplateCode::class) {
                continue;
            }

            $code = $legacyTemplate->getData('code');
            $templateCode->setCode($code);

            $set->saveTemplate($template);
            $em->remove($legacyTemplate);
        }

        // Delete new templates
        $qb = $em->createQueryBuilder();
        $qb
            ->select('t')
            ->from(Template::class, 't')
            ->where('t.name LIKE :name')
            ->setParameter('name', 'SendmailBundle:%')
        ;

        $templates = $qb->getQuery()->getResult();
        /** @var Template $template */
        foreach ($templates as $template) {
            $em->remove($template);
        }
    }
}
