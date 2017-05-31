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

namespace DeskPRO\Bundle\AppBundle\Features;

use Application\DeskPRO\Entity\DataStore;
use Application\DeskPRO\Entity\Template;
use Application\DeskPRO\EntityRepository\Template as TemplateRepository;
use Application\DeskPRO\Templating\Templates\EmailTemplateCode;
use Application\DeskPRO\Templating\Templates\TemplateCode;
use DeskPRO\Bundle\AppBundle\Templating\EmailTemplatesDesc;
use DeskPRO\Bundle\SendmailBundle\Templating\Templates\TemplateSet;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EmailTemplatesFeature.
 */
class EmailTemplatesFeature extends AbstractFeature
{
    protected static $legacyBlocks = [
        'DeskPRO:emails_common:email-header.html.twig'    => 'SendmailBundle:blocks:header.html.twig',
        'DeskPRO:emails_common:email-footer.html.twig'    => 'SendmailBundle:blocks:footer.html.twig',
        'DeskPRO:emails_common:email-custom-css.css.twig' => 'SendmailBundle:blocks:resources.html.twig',
    ];

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

HTML;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisableDescription()
    {
        return 'Disable new Email templates.';
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
        return false;
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
            /** @var Template $previousBlock */
            $template = $set->createCustomTemplate($newTemplateName);

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

    private function convertTemplateCode($code)
    {
        if (preg_match('/<dp:/', $code)) {
            return false;
        }
        if (preg_match_all('/{{[^}]+}}/', $code, $matches)) {
            foreach ($matches[0] as $match) {
                // Only allow phrases without variables, excluding variables or phrases with variables
                if (preg_match('/^{{\s*phrase\s*\([^{]+\)\s*}}$/', $match)) {
                    continue;
                } else {
                    return false;
                }
            }
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

    private function replaceTriggers(EntityManager $em, ContainerInterface $container)
    {
        $emailTriggers = [
            'SendAgentEmail'         => 'SendAgentNewEmail',
            'SendUserEmail'          => 'SendUserNewEmail',
            'SendSpecificUserEmail'  => 'SendSpecificUserNewEmail',
            'SendArbitraryUserEmail' => 'SendArbitraryUserNewEmail',
        ];

        $dbConnection  = $container->get('doctrine')->getConnection('default');
        $templatesDesc = new EmailTemplatesDesc();
        $manifest      = $templatesDesc->getManifest();

        $triggers      = $dbConnection->executeQuery('SELECT `id`,`actions` FROM `ticket_triggers`', []);
        $templatesStmt = $dbConnection->executeQuery('SELECT `name` FROM `templates` WHERE name LIKE ?', ['DeskPRO:emails_%']);
        $templates     = [];
        foreach ($templatesStmt as $template) {
            $templates[] = $template['name'];
        }

        foreach ($triggers as $trigger) {
            $id      = $trigger['id'];
            $changed = false;
            try {
                $actions = json_decode($trigger['actions'], true);
            } catch (\Exception $e) {
                continue;
            }
            foreach ($actions['@DATA']['actions'] as $actionId => $action) {
                if ($action['type'] && isset($emailTriggers[$action['type']])) {
                    if (!in_array($action['options']['template'], $templates)) {
                        $manifestKey = array_search($action['options']['template'], array_column($manifest, 'name'));
                        $info        = $manifest[$manifestKey];

                        $actions['@DATA']['actions'][$actionId]['type']                = $emailTriggers[$action['type']];
                        $actions['@DATA']['actions'][$actionId]['options']['template'] = $info['newTemplate'];
                        $changed                                                       = true;
                    }
                }
            }
            if ($changed) {
                $dbConnection->executeQuery(
                    'UPDATE `ticket_triggers` SET `actions` = ? WHERE `id` = ?',
                    [json_encode($actions), $id]
                );
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
}
