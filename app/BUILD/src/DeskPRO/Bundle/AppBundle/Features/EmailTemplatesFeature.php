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
use Application\DeskPRO\Templating\Templates\TemplateCode;
use DeskPRO\Bundle\SendmailBundle\Templating\Templates\TemplateSet;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class EmailTemplatesFeature.
 */
class EmailTemplatesFeature extends AbstractFeature
{
    protected static $templatesDirectCopy = [
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
        return 'Enable new Email templates';
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
        $this->copyLegacyTemplates($em, $container);
    }

    private function copyLegacyTemplates(EntityManager $em, ContainerInterface $container)
    {
        $set = $this->getTemplateSet($em, $container);

        /** @var TemplateRepository $templateRepo */
        $templateRepo = $em->getRepository(Template::class);
        $templates    = $templateRepo->findBy(['name' => array_keys(self::$templatesDirectCopy)]);

        foreach ($templates as $previousTemplate) {
            /** @var Template $previousTemplate */
            $template = $set->createCustomTemplate(self::$templatesDirectCopy[$previousTemplate->getName()]);

            /** @var TemplateCode $templateCode */
            $templateCode = $template->getTemplateCode();

            $code = $previousTemplate->getTemplateCode();
            if ($previousTemplate->getName() === 'DeskPRO:emails_common:email-custom-css.css.twig') {
                $code = $this->convertResourcesCode($code);
            }
            $templateCode->setCode($code);
            $set->saveTemplate($template);
            $this->saveLegacyTemplate($em, $previousTemplate);
            $em->remove($previousTemplate);
        }

        $em->flush();
    }

    private function convertResourcesCode($code)
    {
        return <<<CODE
{{ default_css | raw }}
<style>
    /* Enter your own custom CSS here */
    $code
</style>
CODE;
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
